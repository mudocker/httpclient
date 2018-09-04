<?php


function error_exception($msg = "", $code = 0){
    new \mdocker\lib\exp\ErrorException($msg,$code);
}