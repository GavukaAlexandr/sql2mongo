<?php

namespace mongoSQLClient;

use MongoDB\Driver\Cursor;
use MongoDB\Client;
use mongoSQLClient\Exception\SQLParseException;

/**
 * Class QueryExecutor
 * @package mongoSQLClient
 */
class QueryExecutor
{
    private $client;
    private $currentDB;
    private $rowsLimit = 50;

    /**
     * QueryExecutor constructor.
     * @param Client $client
     * @param string $currentDB
     */
    public function __construct(Client $client, &$currentDB)
    {
        $this->client = $client;
        $this->currentDB = $currentDB;
    }

    /**
     * Get maximum rows of result
     * @return int
     */
    public function getRowsLimit()
    {
        return $this->rowsLimit;
    }

    /**
     * Set maximum rows of result
     * @param int $rowsLimit
     */
    public function setRowsLimit($rowsLimit)
    {
        $this->rowsLimit = $rowsLimit;
    }

    /**
     * Execute SQL query through MongoDBClient
     * @param bool|array $query
     * @return Cursor
     * @throws SQLParseException
     */
    public function execute($query)
    {
        if (empty($query) || empty($query['db'])) {
            throw new SQLParseException('Query without DB');
        }

        $this->setDefaultQueryLimit($query);

        $collection = $this->client->selectCollection($this->currentDB, $query['db']);
        $cursor = $collection->find($query['filter'], $query['options']);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor;
    }

    /**
     * Set default query limit if it needed
     * @param $query
     */
    private function setDefaultQueryLimit(&$query) {
        if (empty($query['options']) && !isset($query['options']['limit'])) {
            $query['options']['limit'] = $this->rowsLimit;
        }
    }
}