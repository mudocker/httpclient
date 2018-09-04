<?php
use \Curl\MultiCurl;

use mdocker\lib\curl\HttpClient;

$multi_curl = new HttpClient();

$multi_curl->get('https://www.google.com/search', array('q' => 'hello world',));
$multi_curl->get('https://duckduckgo.com/', array('q' => 'hello world',));
$multi_curl->get('https://www.bing.com/search', array('q' => 'hello world',));

$multi_curl->start();
