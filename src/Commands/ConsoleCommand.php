<?php

namespace mongoSQLClient;


interface ConsoleCommand
{
    public function getAvailableCommands();
    public function handle($command);
}