<?php

namespace mongoSQLClient;

use League\CLImate\CLImate;
use MongoDB\Client;
use MongoDB\Database;

class CLIMongoClient implements ConsoleCommand
{
    use Utils;

    private $cli;
    private $mongoClient;
    private $currentDB;
    private $commands = array('help', 'show dbs', 'show collection', 'use');

    /**
     * CLIMongoClient constructor.
     * @param CLImate $cli
     * @param Client $mongoClient
     * @param string $currentDB
     */
    public function __construct(CLImate $cli, Client $mongoClient, &$currentDB)
    {
        $this->cli = $cli;
        $this->mongoClient = $mongoClient;
        $this->currentDB = &$currentDB;
    }

    /**
     * @return mixed
     */
    public function getCurrentDB()
    {
        return $this->currentDB;
    }

    /**
     * @param mixed $currentDB
     */
    public function setCurrentDB($currentDB)
    {
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
            case 'show dbs':
                $dbs = $this->mongoClient->listDatabases();
                foreach ($dbs as $db) {
                    $padding = $this->cli->padding(20)->char('.');
                    $padding->label($db->getName())->result($this->formatBytes($db->getSizeOnDisk()));
                }
                break;
            case 'show collection':
                $currentDB = $this->currentDB;
                if (!empty($currentDB) && $currentDB instanceof Database) {
                    $collections = $this->mongoClient->selectDatabase($currentDB->getDatabaseName())->listCollections();
                    foreach ($collections as $collection) {
                        $this->cli->shout('   ' . $collection->getName());
                    }
                } else {
                    $this->cli->error('Please select db');
                }
                break;
            case 'use':
                $input = $this->cli->input('Please enter DB name >>');
                $dbName = $input->prompt();
                $this->currentDB = $this->mongoClient->selectDatabase($dbName);
                $this->cli->info('Selected ' . $dbName . ' database');
                break;
        }
    }


    /**
     * Print list of available Commands
     */
    private function printListOfCommands()
    {
        $padding = $this->cli->padding(50)->char(' ');
        $padding->label('    <bold><blue>show dbs:</blue></bold>')->result('show database names');
        $padding->label('    <bold><blue>show collection:</blue></bold>')->result('show collections in current database');
        $padding->label('    <bold><blue>use:</blue></bold>')->result('set current database');
    }

}