<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;

// curl "https://httpbin.org/post" -d "foo[0]=bar&foo[1]=baz"

$curl = new Curl();
$curl->post('https://httpbin.org/post', array(
    'foo[0]' => 'bar',
    'foo[1]' => 'baz',
));
