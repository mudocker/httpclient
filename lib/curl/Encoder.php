<?php

namespace mdocker\lib\curl;

class Encoder
{

    public static function encodeJson(){
        $args = func_get_args();
       version_compare(PHP_VERSION, '5.5.0', '<') and  $args = array_slice($args, 0, 2);
        $value = call_user_func_array('json_encode', $args);
        if ((json_last_error() === JSON_ERROR_NONE))  return $value;
         $error_message = function_exists('json_last_error_msg')?  'json_encode error: ' . json_last_error_msg():  $error_message = 'json_encode error';
         error_exception($error_message);
        return $value;
    }
}
