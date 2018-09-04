<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';



$headers = array('Content-Type' => 'application/json', 'X-CUSTOM-HEADER' => 'my-custom-header',);

$multi_curl = new \mdocker\lib\curl\HttpClient();

$multi_curl->beforeSend(function ($instance) use ($headers) {
    foreach ($headers as $key => $value) $instance->setHeader($key, $value);
});

$multi_curl->get('https://www.example.com/');
$multi_curl->get('https://www.example.org/');
$multi_curl->get('https://www.example.net/');
$multi_curl->start();
