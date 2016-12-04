<?php
/**
 * Created by PhpStorm.
 * User: c4r0n0s
 * Date: 27.11.16
 * Time: 3:55
 */

namespace mongoSQLClient;

use League\CLImate\CLImate;

/**
 * Class Utils
 * @package mongoSQLClient
 */
trait Utils
{

    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }


    public function enterQuery(CLImate $cli)
    {
        $input = $cli->input('Please enter your SQL query (Press Ctrl + D after entering) >>>');
        $input->multiLine(); // Will wait for ^D before returning
        $response = $input->prompt();

        return $response;
    }

    public function convertCursorToArray($cursor) {
        $data = array();

        foreach ($cursor as $item) {
            $item['_id'] = $item['_id']->__toString();
            $data[] = $item;
        }

        return $data;
    }

    public function printResultUsingTable(CLImate $cli, $result)
    {
        $data = $this->convertCursorToArray($result);

        if (!empty($data)) {
            $cli->table($data);
        } else {
            $cli->blue()->out('Search returned no results');
        }
    }

    public function printResultUsingJson(CLImate $cli, $result)
    {
        $data = json_encode(iterator_to_array($result));

        if (!empty($data)) {
            $cli->json($data);
        } else {
            $cli->blue()->out('Search returned no results');
        }
    }
}