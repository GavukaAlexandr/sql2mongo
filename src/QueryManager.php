<?php

namespace mongoSQLClient;

use SqlParser\Parser;
use SqlParser\Statements\SelectStatement;
use mongoSQLClient\Exception\SQLParseException;

/**
 * Class QueryManager
 * @package mongoSQLClient
 */
class QueryManager
{
    private static $mappingChart = array(
        ' or ' => ' $or ',
        ' and ' => ' $and ',
        ' <> ' => ' $ne ',
        ' > ' => ' $gt ',
        ' >= ' => ' $gte ',
        ' < ' => ' $lt ',
        ' <= ' => ' $lte ',
        ' = ' => ' = ',
    );

    private $sqlQuery;

    /**
     * QueryManager constructor.
     * @param $query
     */
    public function __construct($query)
    {
        $this->sqlQuery = $query;
    }

    /**
     * Return SQL query
     * @return mixed
     */
    public function getSqlQuery()
    {
        return $this->sqlQuery;
    }

    /**
     * Set SQL query
     * @param mixed $sqlQuery
     */
    public function setSqlQuery($sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
    }

    /**
     * Transform SQL query to Mongo query
     * @return array|bool
     * @throws SQLParseException
     */
    public function convertSQLToMongoQuery()
    {
        $parser = new Parser($this->sqlQuery);

        if (empty($parser->statements)) {
            throw new SQLParseException("SQL query isn't valid \n");
        }

        /**
         * @var SelectStatement
         */
        $statements = $parser->statements[0];
        $mongoQuery = array();

        if ($statements instanceof SelectStatement) {

            if ($statements !== null && (!$statements->validateClauseOrder($parser, $parser->list) || empty($statements->from))) {
                throw new SQLParseException('SQL query isn\'t valid');
            }

            $db = $statements->from[0]->table;
            $fields = $this->parseSelectStatement($statements);
            $mongoWhere = !empty($statements->where) ? $this->parseWhereStatement($statements->where) : array();
            $orders = $this->parseOrderStatement($statements);

            $mongoQuery = $this->createMongoQuery($statements, $db, $mongoWhere, $fields, $orders);
        }

        return $mongoQuery;
    }


    /**
     * Convert SQL SELECT to mongo options
     * @param SelectStatement $statements
     * @return array
     */
    private function parseSelectStatement(SelectStatement $statements)
    {
        $fields = array();

        if (sizeof($statements->expr) !== 1 || $statements->expr[0]->expr !== '*') {
            foreach ($statements->expr as $field) {
                $fields[$field->expr] = 1;
            }
        }

        return $fields;
    }

    /**
     * Convert SQL WHERE to mongo filter
     * @param SelectStatement $whereStatement
     * @return mixed|SelectStatement|string
     */
    private function parseWhereStatement($whereStatement)
    {
        $rawWhere = '';
        foreach ($whereStatement as $condition) {
            $rawWhere .= $condition . ' ';
        }

        $whereStatement = $this->replaceSQLCondToMongo($rawWhere);
        foreach ($this::$mappingChart as $operation) {
            $this->processedStatement($whereStatement, $operation);
        }

        return $whereStatement;
    }

    /**
     * Replace SQL operators to mongo operators
     * @param $query
     * @return mixed|string
     */
    private function replaceSQLCondToMongo($query)
    {
        $query = strtolower($query);
        foreach (self::$mappingChart as $key => $operation) {
            $query = str_replace($key, $operation, $query);
        }

        return $query;
    }

    /**
     * Convert SQL statement to mongo criteria
     * @param $statements
     * @param $operation
     */
    private function processedStatement(&$statements, $operation)
    {
        if (is_string($statements)) {
            $statements = $this->parseConditions($statements, $operation);
        } elseif (is_array($statements)) {
            foreach ($statements as &$statement) {
                $this->processedStatement($statement, $operation);
            }
        }
    }

    /**
     * Convert SQL conditions to mongo criteria
     * @param $statements
     * @param $operation
     * @return array|mixed
     */
    private function parseConditions($statements, $operation)
    {
        $statements = explode($operation, $statements);
        if (sizeof($statements) > 1) {
            if (in_array($operation, [' $or ', ' $and '])) {
                $this->parseLogicConditions($statements, $operation);
            } elseif (in_array($operation, [' $ne ', ' $gt ', ' $gte ', ' $lt ', ' $lte '])) {
                $this->parseCompareConditions($statements, $operation);
            } elseif ($operation === ' = ') {
                $this->parseEqualCondition($statements);
            }
        } else {
            $statements = reset($statements);
        }

        return $statements;
    }

    /**
     * Convert SQL logic conditions to mongo criteria
     * @param $statements
     * @param $operation
     */
    private function parseLogicConditions(&$statements, $operation)
    {
        $tmp = array();
        $operation = trim($operation);
        $tmp[$operation] = array();
        foreach ($statements as &$statement) {
            $tmp[$operation][] = $statement;
        }
        $statements = $tmp;
    }

    /**
     * Convert SQL compare conditions to mongo criteria
     * @param $statements
     * @param $operation
     */
    private function parseCompareConditions(&$statements, $operation)
    {
        $tmp = array();
        $operation = trim($operation);
        $value = (is_numeric(trim($statements[1]))) ? (float)$statements[1] : str_replace('\'', '', $statements[1]);
        //Remove double quotes
        $value = (is_string($value)) ? str_replace('"', '', $value) : $value;
        $tmp[$statements[0]] = array($operation => $value);
        $statements = $tmp;
    }

    /**
     * Convert SQL '=' conditions to mongo criteria
     * @param $statements
     */
    private function parseEqualCondition(&$statements)
    {
        $value = (is_numeric(trim($statements[1]))) ? (float)$statements[1] : $statements[1];
        $tmp[$statements[0]] = $value;
        $statements = $tmp;
    }

    /**
     * Convert SQL order conditions to mongo criteria
     * @param SelectStatement $statements
     * @return array
     */
    private function parseOrderStatement(SelectStatement $statements)
    {
        $orders = array();

        if (!empty($statements->order)) {
            foreach ($statements->order as $order) {
                $orders[$order->expr->column] = $order->type === 'ASC' ? 1 : -1;
            }
        }

        return $orders;
    }

    /**
     * Merge all mongo criterias
     * @param SelectStatement $statements
     * @param $db
     * @param $mongoWhere
     * @param $fields
     * @param $orders
     * @return array
     */
    private function createMongoQuery(SelectStatement $statements, $db, $mongoWhere, $fields, $orders)
    {
        $mongoQuery = array(
            'db' => $db,
            'filter' => $mongoWhere,
            'options' => array(
                'projection' => $fields,
                'sort' => $orders,
            ),
        );

        if (isset($statements->limit) && !empty($statements->limit)) {
            $options = &$mongoQuery['options'];
            $this->setOptionProperty($statements->limit, $options, 'rowCount', 'limit');
            $this->setOptionProperty($statements->limit, $options, 'offset', 'skip');
        }

        //Remove empty elements
        $mongoQuery['options'] = array_filter($mongoQuery['options']);

        return $mongoQuery;
    }

    /**
     * Set mongo option properties
     * @param $limit
     * @param $options
     * @param $field
     * @param $key
     */
    private function setOptionProperty($limit, &$options, $field, $key)
    {
        if (!empty($limit->{$field}) && is_int($limit->{$field})) {
            $options[$key] = (int)$limit->{$field};
        }
    }
}