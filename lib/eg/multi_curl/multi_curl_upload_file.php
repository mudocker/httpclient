<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->post('https://httpbin.org/post', array('image' => new CURLFile('the-lorax.jpg'),));
$multi_curl->post('https://httpbin.org/post', array('image' => new CURLFile('swomee-swans.jpg'),));
$multi_curl->post('https://httpbin.org/post', array('image' => new CURLFile('truffula-trees.jpg'),));
$multi_curl->start();
