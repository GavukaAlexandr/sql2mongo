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
     * @return int
     */
    public function getRowsLimit()
    {
        return $this->rowsLimit;
    }

    /**
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

        if (empty($query['options']) && !isset($query['options']['limit'])) {
            $query['options']['limit'] = $this->rowsLimit;
        }

        //TODO move Current DB to helper state Class
        $collection = $this->client->selectCollection($this->currentDB, $query['db']);
        $cursor = $collection->find($query['filter'], $query['options']);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor;
    }
}