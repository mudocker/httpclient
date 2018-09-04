<?php
require dirname(dirname(__DIR__)) . '/../vendor/autoload.php';
$callback = function ($instance, $tmpfile) {
    $save_to_path = '/tmp/' . basename($instance->url);
    $fh = fopen($save_to_path, 'wb');
    stream_copy_to_stream($tmpfile, $fh);
    fclose($fh);
};

$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->download('https://secure.php.net/images/logos/php-med-trans.png', $callback);
$multi_curl->download('https://upload.wikimedia.org/wikipedia/commons/c/c1/PHP_Logo.png', $callback);
$multi_curl->start();
