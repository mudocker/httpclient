<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->del('https://httpbin.org/delete', array('id' => '123',));
$multi_curl->del('https://httpbin.org/delete', array('id' => '456',));
$multi_curl->start();
