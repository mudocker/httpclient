<?php

namespace mdocker\lib\curl;



class ArrayUtil
{

    public static function is_array_assoc($array){
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }


    public static function is_array_multidim($array){
        return !is_array($array)?  false:(bool)count(array_filter($array, 'is_array'));
    }

    public static function array_flatten_multidim($array, $prefix = false)
    {
        $return = array();
        if (is_array($array) || is_object($array)) {
            if (empty($array)) $return[$prefix] = '';
             else {
                foreach ($array as $key => $value) {
                    if (is_scalar($value)) $prefix? $return[$prefix . '[' . $key . ']'] = $value:$return[$key] = $value;
                     else {
                        $value instanceof \CURLFile?  $return[$key] = $value: $return = array_merge($return, self::array_flatten_multidim($value, $prefix ? $prefix . '[' . $key . ']' : $key));
                    }
                }
            }
        } elseif ($array === null)  $return[$prefix] = $array;

        return $return;
    }
}
