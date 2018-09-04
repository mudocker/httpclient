<?php
require dirname(__DIR__) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;

$myfile = curl_file_create('cats.jpg', 'image/jpeg', 'test_name');

$curl = new Curl();

$curl->post('https://httpbin.org/post', array('myfile' => $myfile,));

if ($curl->error) echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
else echo 'Success' . "\n";

