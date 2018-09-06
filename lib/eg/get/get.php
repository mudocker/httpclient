<?php
use mdocker\lib\curl\multi\mcurl;

require  dirname(__DIR__).'/autoload.php';

$mcurl=new mcurl();
$mcurl->get('http://www.topthink.com/');
$mcurl->get('http://www.hao123.com/?1536217914');
$mcurl->get("https://secure.php.net/manual/en/function.curl-multi-add-handle.php");

$data=$mcurl->start();


$data=1;