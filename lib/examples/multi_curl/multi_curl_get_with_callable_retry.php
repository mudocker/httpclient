<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';



$max_retries = 3;

$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->setRetry(function ($instance) use ($max_retries) {
    return $instance->retries < $max_retries;
});
$multi_curl->complete(function ($instance) {
    echo 'call to "' . $instance->url . '" completed.' . "\n";
    echo 'attempts: ' . $instance->attempts . "\n";
    echo 'retries: ' . $instance->retries . "\n";
});

$multi_curl->get('https://httpbin.org/status/503?a');
$multi_curl->get('https://httpbin.org/status/503?b');
$multi_curl->get('https://httpbin.org/status/503?c');

$multi_curl->start();
