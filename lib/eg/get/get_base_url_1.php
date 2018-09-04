<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$curl = new Curl('https://httpbin.org/get');
for ($i = 1; $i <= 10; $i++) $curl->get(array('page' => $i,));


