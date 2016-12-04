<?php

namespace mongoSQLClient;

use League\CLImate\CLImate;
use MongoDB\Client;

class CLIQueryProcessed implements ConsoleCommand
{
    use Utils;

    private $cli;
    private $mongoClient;
    private $currentDB;
    private $commands = array('help', 'SQL');

    /**
     * CLIQueryProcessed constructor.
     * @param CLImate $cli
     * @param Client $mongoClient
     * @param string $currentDB
     */
    public function __construct(CLImate $cli, Client &$mongoClient, &$currentDB)
    {
        $this->cli = $cli;
        $this->mongoClient = $mongoClient;
        $this->currentDB = $currentDB;
    }

    /**
     * Return list of available Commands
     * @return array
     */
    public function getAvailableCommands()
    {
        return $this->commands;
    }

    /**
     * Handle and execute Commands
     * @param $command
     */
    public function handle($command)
    {
        if ($command === 'help') {
            $this->printListOfCommands();
        } elseif ($command === 'SQL') {
            $response = $this->enterQuery($this->cli);
            $result = $this->executeSQLQuery($response);
            if (!empty($result)) {
                $this->printResult($this->cli, $result);
            }
        }
    }

    /**
     * Execute SQL query through MongoDBClient
     * @param $response
     * @return mixed
     */
    private function executeSQLQuery($response) {
        $queryManager = new QueryManager($response);
        $mongoQuery = $queryManager->convertSQLToMongoQuery();

        if (!empty($mongoQuery)) {
            $queryExecutor = new QueryExecutor($this->mongoClient, $this->currentDB);
            $result = $queryExecutor->execute($mongoQuery);

            return $result;
        }

        return false;
    }

    /**
     * Print list of available Commands
     */
    private function printListOfCommands()
    {
        $padding = $this->cli->padding(50)->char(' ');
        $padding->label('    <bold><blue>SQL:</blue></bold>')->result('Enter SQL query');
    }

}