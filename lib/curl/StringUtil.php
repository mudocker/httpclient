<?php

namespace mdocker\lib\curl;

class StringUtil{

    public static function startsWith($haystack, $needle){
        return \mb_substr($haystack, 0, \mb_strlen($needle)) === $needle;
    }
}
