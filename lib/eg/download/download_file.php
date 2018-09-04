<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;
$curl = new Curl();
$curl->download('https://secure.php.net/images/logos/php-med-trans.png', '/tmp/php-med-trans.png');
