<?php

namespace mongoSQLClient\Exception;

use Exception;

class SQLParseException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}