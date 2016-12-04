<?php

namespace mongoSQLClient;

use PHPUnit\Framework\TestCase;

class StackTestTest extends TestCase
{
    private $sqlQuery = 'SELECT * FROM test';

    public function testConstructor()
    {
        $queryManager = new QueryManager($this->sqlQuery);

        $this->assertNotNull($queryManager);
        $this->assertEquals($this->sqlQuery, $queryManager->getSqlQuery());
    }

    public function testSetterAndGetter()
    {
        $updatedSqlQuery = 'SELECT * FROM test WHERE name = "John"';
        $queryManager = new QueryManager($this->sqlQuery);

        $this->assertNotNull($queryManager);
        $this->assertEquals($this->sqlQuery, $queryManager->getSqlQuery());

        $queryManager->setSqlQuery($updatedSqlQuery);

        $this->assertNotNull($queryManager);
        $this->assertEquals($updatedSqlQuery, $queryManager->getSqlQuery());
    }

    /**
     * @expectedException mongoSQLClient\Exception\SQLParseException
     * @expectedExceptionMessage SQL query isn't valid
     */
    public function testEmptyQuery()
    {
        $emptyQuery = '';
        $queryManager = new QueryManager($emptyQuery);
        $queryManager->convertSQLToMongoQuery();
    }

    public function testNotValidQuery()
    {
        $notValidQuery = 'SELECT * FROM restaurants WHERE1 name = "John" LIMIT a OFFSET b';
        $queryManager = new QueryManager($notValidQuery);
        $mongoQuery = $queryManager->convertSQLToMongoQuery();

        $this->assertEquals($mongoQuery['db'], 'restaurants');
        $this->assertEmpty($mongoQuery['filter']);
        $this->assertEmpty($mongoQuery['options']);
    }

    public function testValidQuery()
    {
        $mongoWhere = array(
            '$or' => array(
                array(
                    '$and' => array(
                        array('grades.score' => array('$gt' => 4)),
                        array('name' => array('$ne' => 'test')),
                    ),
                ),
                array('grades.score' => array('$lte' => 8)),
            ),
        );

        $validQuery = "SELECT name, borough FROM restaurants WHERE grades.score > 4 AND name <> \"test\" OR grades.score <= 8 ORDER BY borough DESC, name ASC LIMIT 5 OFFSET 1";
        $queryManager = new QueryManager($validQuery);
        $mongoQuery = $queryManager->convertSQLToMongoQuery();

        $this->assertEquals($mongoQuery['db'], 'restaurants');
        $this->assertNotEmpty($mongoQuery['options']);
        $this->assertEquals(sizeof($mongoQuery['options']['projection']), 2);
        $this->assertEquals($mongoQuery['options']['projection'], array('name' => 1, 'borough' => 1));
        $this->assertNotEmpty($mongoQuery['filter']);
        $this->assertEquals($mongoQuery['filter'], $mongoWhere);
    }
}
