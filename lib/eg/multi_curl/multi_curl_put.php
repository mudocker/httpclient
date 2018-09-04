<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';


$multi_curl =new \mdocker\lib\curl\HttpClient();

$multi_curl->put('https://httpbin.org/put', array(
    'id' => '123',
    'subject' => 'hello',
    'body' => 'hello',
));
$multi_curl->put('https://httpbin.org/put', array(
    'id' => '456',
    'subject' => 'hello',
    'body' => 'hello',
));

$multi_curl->start();
