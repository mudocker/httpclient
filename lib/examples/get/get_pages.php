<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$curl = new Curl();
for ($i = 1; $i <= 10; $i++) $curl->get('https://httpbin.org/get', array('page' => $i,));


