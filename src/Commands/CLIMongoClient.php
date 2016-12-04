<?php

namespace mongoSQLClient;

use League\CLImate\CLImate;
use MongoDB\Client;
use League\CLImate\TerminalObject\Dynamic\Padding;

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
                $this->showDatabases();
                break;
            case 'show collection':
                $this->showCollections();
                break;
            case 'use':
                $this->selectCurrentDatabase();
                break;
        }
    }

    /**
     * Print list of available Commands
     */
    private function printListOfCommands()
    {
        /**
         * @var Padding
         */
        $padding = $this->cli->padding(50)->char(' ');
        $padding->label('    <bold><blue>show dbs:</blue></bold>')->result('show database names');
        $padding->label('    <bold><blue>show collection:</blue></bold>')->result('show collections in current database');
        $padding->label('    <bold><blue>use:</blue></bold>')->result('set current database');
    }

    /**
     * Print list of databases
     */
    private function showDatabases()
    {
        $dbs = $this->mongoClient->listDatabases();
        foreach ($dbs as $db) {
            $padding = $this->cli->padding(20)->char('.');
            $padding->label($db->getName())->result($this->formatBytes($db->getSizeOnDisk()));
        }
    }

    /**
     * Print list of collections in current mongo DB
     */
    private function showCollections()
    {
        $currentDB = $this->currentDB;
        if (!empty($currentDB)) {
            $collections = $this->mongoClient->selectDatabase($currentDB)->listCollections();
            foreach ($collections as $collection) {
                $this->cli->shout('   ' . $collection->getName());
            }
        } else {
            $this->cli->error('Please select db');
        }
    }

    /**
     * Select current mongo DB
     */
    private function selectCurrentDatabase()
    {
        $input = $this->cli->input('Please enter DB name >>');
        $dbName = $input->prompt();
        $this->currentDB = $this->mongoClient->selectDatabase($dbName)->getDatabaseName();
        $this->cli->info('Selected ' . $dbName . ' database');
    }
}