<?php
require dirname(__DIR__) . '/../vendor/autoload.php';

use mdocker\lib\curl\Curl;

$my_xml_decoder = function ($response) {
    $xml_obj = @simplexml_load_string($response);
    $xml_obj !== false and  $response = json_decode(json_encode($xml_obj), true);
    return $response;
};

$curl = new Curl();
$curl->setXmlDecoder($my_xml_decoder);
$curl->get('https://httpbin.org/xml');

if ($curl->error) echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
