<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;

// curl --request GET "https://httpbin.org/get?key=value"

$curl = new Curl();
$curl->get('https://httpbin.org/get', array('key' => 'value',));

if ($curl->error) echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
 else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
