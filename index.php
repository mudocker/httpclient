<?php

use mdocker\lib\curl\mcurl;
require __DIR__.'/vendor/autoload.php';
$urls=array('http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203',
    'http://www.hao123.com/?1536137203'
);
$mcurl=new mcurl($urls);
$data=$mcurl->get();
var_dump($data);