<?php

require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;

$curl = new Curl();
$curl->get('https://secure.php.net/');
if ($curl->error) echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
else {
    echo 'Response cookies:' . "\n";
    var_dump($curl->responseCookies);
    var_dump($curl->getResponseCookies());
}
