<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$curl = new Curl();
$curl->setCookie('foo', 'bar');
$curl->get('https://httpbin.org/cookies');
var_dump($curl->response->cookies->foo === 'bar');
