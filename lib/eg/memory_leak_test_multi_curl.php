<?php
require dirname(__DIR__) . '/../vendor/autoload.php';

use \Curl\MultiCurl;

$multi_curl =new \mdocker\lib\curl\HttpClient();

for ($i = 0; $i < 10; $i++) {
    for ($j = 0; $j <= 500; $j++) {
        $multi_curl->get('http://127.0.0.1:8000/');
    }
    $multi_curl->start();
    echo 'memory ' . $i . ': ' . memory_get_usage(true) . "\n";
}
