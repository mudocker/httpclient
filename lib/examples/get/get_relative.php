<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$curl = new Curl('https://www.example.com/api/');
// https://www.example.com/api/test?key=value
$response = $curl->get('test', array('key' => 'value',));
assert('https://www.example.com/api/test?key=value' === $curl->url);
assert($curl->url === $curl->effectiveUrl);

// https://www.example.com/root?key=value
$response = $curl->get('/root', array('key' => 'value',));
assert('https://www.example.com/root?key=value' === $curl->url);
assert($curl->url === $curl->effectiveUrl);
