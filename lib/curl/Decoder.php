<?php

namespace mdocker\lib\curl;

class Decoder{

    public static function decodeJson(){
        $args = func_get_args();
        version_compare(PHP_VERSION, '5.4.0', '<') and  $args = array_slice($args, 0, 3);
        $response = call_user_func_array('json_decode', $args);
        $response === null and  $response = $args['0'];
        return $response;
    }


    public static function decodeXml()
    {
        $args = func_get_args();
        $response = @call_user_func_array('simplexml_load_string', $args);
        $response === false and  $response = $args['0'];
        return $response;
    }
}
