<?php

namespace mongoSQLClient;

use PHPUnit\Framework\TestCase;

use MongoDB\Client;


class QueryExecutorTest extends TestCase
{
    use Utils;

    private $currentDB = 'test';

    public function testConstructor()
    {
        $client = new Client();
        $rowsLimit = 42;

        $queryExecutor = new QueryExecutor($client, $this->currentDB);
        $queryExecutor->setRowsLimit($rowsLimit);

        $this->assertNotNull($queryExecutor);
        $this->assertEquals($queryExecutor->getRowsLimit(), $rowsLimit);
    }

    /**
     * @expectedException MongoDB\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected "limit" option to have type "integer" but found "string"
     */
    public function testExecuteNotValidQuery() {
        $client = new Client();
        $queryLimit = '3';
        $grade = 40;
        $mongoQuery = array(
            'db' => 'restaurants',
            'filter' => array(
                'grades.score' => array(
                    '$gt' => $grade,
                ),
            ),
            'options' => array(
                'projection' => array(
                    'name' => 1,
                    'borough' => 1,
                    'grades.score' => 1,
                ),
                'limit' => $queryLimit,
                'skip' => 5,
            ),
        );

        $queryExecutor = new QueryExecutor($client, $this->currentDB);
        $queryExecutor->execute($mongoQuery);
    }

    public function testExecuteValidQuery() {
        $client = new Client();
        $queryLimit = 3;
        $grade = 40;
        $mongoQuery = array(
            'db' => 'restaurants',
            'filter' => array(
                'grades.score' => array(
                  '$gt' => $grade,
                ),
            ),
            'options' => array(
                'projection' => array(
                    'name' => 1,
                    'borough' => 1,
                    'grades.score' => 1,
                ),
                'limit' => $queryLimit,
                'skip' => 5,
            ),
        );

        $queryExecutor = new QueryExecutor($client, $this->currentDB);
        $cursor = $queryExecutor->execute($mongoQuery);
        $data = $this->convertCursorToArray($cursor);

        $this->assertNotEmpty($data);
        $this->assertEquals(sizeof($data), $queryLimit);
        $this->assertGreaterThan($grade, max($data[0]['grades'])['score']);
        $this->assertGreaterThan($grade, max($data[1]['grades'])['score']);
        $this->assertGreaterThan($grade, max($data[2]['grades'])['score']);
    }

}