<?php




$multi_curl =new \mdocker\lib\curl\HttpClient();
$multi_curl->success(function ($instance) {
    echo 'call to "' . $instance->url . '" was successful.' . "\n";
    echo 'response: ' . $instance->response . "\n";
});
$multi_curl->error(function ($instance) {
    echo 'call to "' . $instance->url . '" was unsuccessful.' . "\n";
    echo 'error code: ' . $instance->errorCode . "\n";
    echo 'error message: ' . $instance->errorMessage . "\n";
});
$multi_curl->complete(function ($instance) {
    echo 'call to "' . $instance->url . '" completed.' . "\n";
});

$multi_curl->get('https://www.google.com/search', array(
    'q' => 'hello world',
));
$multi_curl->get('https://duckduckgo.com/', array(
    'q' => 'hello world',
));
$multi_curl->get('https://www.bing.com/search', array(
    'q' => 'hello world',
));

$multi_curl->start();
