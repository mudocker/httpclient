<?php
/**
 * Created by PhpStorm.
 * User: ACER-VERITON
 * Date: 2018/9/4
 * Time: 10:04
 */

namespace mdocker\lib;


class TTT
{

   function __construct(){
       $client = new HttpClient('example.com');
       if (!$client->get('/')) {
           die('An error occurred: '.$client->getError());
       }
       $pageContents = $client->getContent();


    }
}