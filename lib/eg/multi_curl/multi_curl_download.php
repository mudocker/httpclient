<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';



$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->download('https://secure.php.net/images/logos/php-med-trans.png', '/tmp/php-med-trans.png');
$multi_curl->download('https://upload.wikimedia.org/wikipedia/commons/c/c1/PHP_Logo.png', '/tmp/PHP_Logo.png');
$multi_curl->start();
