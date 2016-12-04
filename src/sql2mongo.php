#!/usr/bin/env php
<?php
namespace mongoSQLClient;

set_time_limit(300);

require __DIR__ . '/../vendor/autoload.php';
include_once 'QueryManager.php';
include_once 'QueryExecutor.php';
include_once 'Commands/ConsoleCommand.php';
include_once 'Commands/CLIMongoClient.php';
include_once 'Commands/CLIQueryProcessed.php';
include_once 'Utils.php';

use League\CLImate\CLImate;
use MongoDB\Client;

if (PHP_SAPI !== 'cli') {
    return;
}
$climate = new CLImate;
$client = new Client();

$climate->info('SQL2MongoDB shell version: 0.1.0');
$cliText = '<cyan>Please enter <bold>help</bold> to show list of Commands or <bold>exit</bold> to exit from app >></cyan>';
$cli = $climate->input($cliText);
$currentDB = 'test';

$cli->accept(function ($response) use ($climate, &$client, &$currentDB) {
    if ($response === 'exit') {
        return true;
    }

    $commandHandlers = array(
        new CLIMongoClient($climate, $client, $currentDB),
        new CLIQueryProcessed($climate, $client, $currentDB)
    );

    foreach ($commandHandlers as $commandHandler) {


        if ($commandHandler instanceof ConsoleCommand && in_array($response, $commandHandler->getAvailableCommands())) {
            $commandHandler->handle($response);
        }
    }

    return false;
});

$cli->prompt();
