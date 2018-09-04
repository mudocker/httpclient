<?php
require dirname(__DIR__) . '/../vendor/autoload.php';
use mdocker\lib\curl\Curl;
$pages = 10;
$q = 'coffee';
$curl = new Curl();
$curl->setUrl('https://www.example.com/search');
for ($i = 1; $i <= $pages; $i++) $curl->get(array('q' => $q, 'page' => $i,));  // https://www.example.com/search?q=coffee&page=N

