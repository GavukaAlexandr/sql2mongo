<?php

namespace mongoSQLClient;

use League\CLImate\CLImate;
use MongoDB\Client;
use \MongoDB\Driver\Cursor;

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
        switch ($command) {
            case 'help':
                $this->printListOfCommands();
                break;
            case 'SQL':
                $result = $this->processedSQLQuery();
                $this->chooseMethodOfPrint($result);
                break;
        }
    }

    /**
     * Print list of available Commands
     */
    private function printListOfCommands()
    {
        $padding = $this->cli->padding(50)->char(' ');
        $padding->label('    <bold><blue>SQL:</blue></bold>')->result('Enter SQL query');
    }

    /**
     * Processed SQL query;
     * @return Cursor|array
     */
    private function processedSQLQuery()
    {
        $response = $this->enterQuery($this->cli);
        $result = $this->executeSQLQuery($response);

        return $result;
    }

    /**
     * Execute SQL query through MongoDBClient
     * @param $response
     * @return Cursor|array
     */
    private function executeSQLQuery($response)
    {
        $queryManager = new QueryManager($response);
        $mongoQuery = $queryManager->convertSQLToMongoQuery();

        if (!empty($mongoQuery)) {
            $queryExecutor = new QueryExecutor($this->mongoClient, $this->currentDB);
            $result = $queryExecutor->execute($mongoQuery);

            return $result;
        }

        return array();
    }

    /**
     * Choose method of result print
     * @param $result
     */
    private function chooseMethodOfPrint($result)
    {
        if (!empty($result)) {
            $options = ['Table', 'JSON'];
            $this->cli->info('Table method does\'n support print sub arrays');
            $input = $this->cli->radio('Please send me one of the following:', $options);
            $method = $input->prompt();

            switch ($method) {
                case 'Table':
                    $this->printResultUsingTable($this->cli, $result);
                    break;
                case 'JSON':
                    $this->printResultUsingJson($this->cli, $result);
                    break;
            }
        } else {
            $this->cli->blue()->out('Search returned no results');
        }
    }

}