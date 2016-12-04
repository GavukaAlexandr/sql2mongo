<?php

namespace mongoSQLClient;

use SqlParser\Parser;
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

        $statements = $parser->statements[0];
        // TODO add validation

        if ($statements !== null && (!$statements->validateClauseOrder($parser, $parser->list) || empty($statements->from))) {
            throw new SQLParseException('SQL query isn\'t valid');
        }
        $db = $statements->from[0]->table;
        $fields = array();
        $orders = array();

        $mongoWhere = !empty($statements->where) ? $this->parseWhereStatement($statements->where) : array();


        if (sizeof($statements->expr) === 1 && $statements->expr[0]->expr === '*') {
            $fields = array();
        } else {
            foreach ($statements->expr as $field) {
                $fields[$field->expr] = 1;
            }
        }

        if (empty($statements->order)) {
            $orders = array();
        } else {
            foreach ($statements->order as $order) {
                $orders[$order->expr->column] = $order->type === 'ASC' ? 1 : -1;
            }
        }

        $mongoQuery = array(
            'db' => $db,
            'filter' => $mongoWhere,
            'options' => array(
                'projection' => $fields,
                'sort' => $orders,
            ),
        );

        if (isset($statements->limit) && !empty($statements->limit)) {
            if (!empty($statements->limit->rowCount) && is_int($statements->limit->rowCount)) {
                $mongoQuery['options']['limit'] = (int)$statements->limit->rowCount;
            }
            if (!empty($statements->limit->offset) && is_int($statements->limit->offset)) {
                $mongoQuery['options']['skip'] = (int)$statements->limit->offset;
            }
        }

        //Remove empty elements
        $mongoQuery['options'] = array_filter($mongoQuery['options']);

        return $mongoQuery;
    }

    private function parseWhereStatement($statements)
    {
        $rawWhere = '';
        foreach ($statements as $condition) {
            $rawWhere .= $condition . ' ';
        }

        $statements = $this->replaceSQLCondToMongo($rawWhere);
        foreach ($this::$mappingChart as $operation) {
            $this->processedStatement($statements, $operation);
        }

        return $statements;
    }

    private function replaceSQLCondToMongo($query)
    {
        $query = strtolower($query);
        foreach (self::$mappingChart as $key => $operation) {
            $query = str_replace($key, $operation, $query);
        }

        return $query;
    }

    private function processedStatement(&$statements, $operation)
    {
        if (is_string($statements)) {
            $statements = $this->parseCondition($statements, $operation);
        } elseif (is_array($statements)) {
            foreach ($statements as &$statement) {
                $this->processedStatement($statement, $operation);
            }
        }
    }

    private function parseCondition($statements, $operation)
    {
        $statements = explode($operation, $statements);
        if (sizeof($statements) > 1) {
            if (in_array($operation, [' $or ', ' $and '])) {
                $operation = trim($operation);
                $tmp[$operation] = array();
                foreach ($statements as &$statement) {
                    $tmp[$operation][] = $statement;
                }
                $statements = $tmp;
            } elseif (in_array($operation, [' $ne ', ' $gt ', ' $gte ', ' $lt ', ' $lte '])) {
                $operation = trim($operation);
                $value = (is_numeric(trim($statements[1]))) ? (float)$statements[1] : str_replace('\'', '', $statements[1]);
                //Remove double quotes
                $value = (is_string($value)) ? str_replace('"', '', $value) : $value;
                $tmp[$statements[0]] = array($operation => $value);
                $statements = $tmp;
            } elseif ($operation === ' = ') {
                $value = (is_numeric(trim($statements[1]))) ? (float)$statements[1] : $statements[1];
                $tmp[$statements[0]] = $value;
                $statements = $tmp;
            }
        } else {
            $statements = reset($statements);
        }

        return $statements;
    }

}