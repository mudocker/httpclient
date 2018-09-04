<?php
/**
 * Created by PhpStorm.
 * User: ACER-VERITON
 * Date: 2018/9/4
 * Time: 13:19
 */

namespace mdocker\lib\exp;


class ErrorException extends \ErrorException
{


     function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous){
         parent::__construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous);
      }
}