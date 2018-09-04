<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

$multi_curl =new \mdocker\lib\curl\HttpClient();

$multi_curl->patch('https://httpbin.org/patch', array(
    'id' => '123',
    'body' => 'hello world!',
));
$multi_curl->patch('https://httpbin.org/patch', array(
    'id' => '456',
    'body' => 'hello world!',
));

$multi_curl->start();
