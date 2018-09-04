<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$curl = new Curl();
$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
$curl->post('https://www.example.com/login/', array('username' => 'myusername', 'password' => 'mypassword',));

$curl = new Curl();
$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
$curl->post('https://www.example.com/login/', array('username' => 'myusername', 'password' => 'mypassword',), false);
