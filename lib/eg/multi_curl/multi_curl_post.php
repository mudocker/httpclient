<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

$httpclient =new \mdocker\lib\curl\HttpClient();

$httpclient->post('https://httpbin.org/post', array(
    'to' => 'alice',
    'subject' => 'hi',
    'body' => 'hi Alice',
));
$httpclient->post('https://httpbin.org/post', array(
    'to' => 'bob',
    'subject' => 'hi',
    'body' => 'hi Bob',
));

$httpclient->start();
