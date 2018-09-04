<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;

// curl \
//     -X POST \
//     -d "{"id":"1","content":"Hello world!","date":"2015-06-30 19:42:21"}" \
//     "https://httpbin.org/post"

$data = json_encode(array(
    'id' => '1',
    'content' => 'Hello world!',
    'date' => date('Y-m-d H:i:s'),
), JSON_UNESCAPED_UNICODE);

$curl = new Curl();
$curl->setHeader('Content-Type', 'application/json');
$curl->post('https://httpbin.org/post', $data);
var_dump($curl->response->json);
