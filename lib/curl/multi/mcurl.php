<?php
/**
 * Created by IntelliJ IDEA.
 * User: ACER-VERITON
 * Date: 2018/9/5
 * Time: 20:04
 */

namespace mdocker\lib\curl\multi;


class mcurl
{
    public $handle;
    public $active= null;
    public $mrc;
    public $curl=array();
    public $urls=[];
    public $result=[];

    function get($urls){
        array_push($this->urls,$urls);
    }
    function start(){
        $this->add_ch();
        $this->read();
        return   $this->getResponse();
    }


    function __construct(){
        $this->handle =  curl_multi_init();
    }
    function add_ch(){
        foreach ($this->urls as $key => $v) {
            $this->curl[$key]=$this->getCh($v);
            curl_multi_add_handle($this->handle,$this->curl[$key]);
        }
    }
    function read(){

        do {
            $status = curl_multi_exec($this->handle, $this->active);
            $info = curl_multi_info_read($this->handle);
            false !== $info and  var_dump($info);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $this->active);
    }
    function getResponse(){
        foreach ($this->urls as $key => $v) $this->result[$key] = curl_multi_getcontent($this->curl[$key]);
        return $this->result;
    }
     function __destruct(){
       foreach ($this->curl as $v) {
           $success_0=curl_multi_remove_handle($this->handle, $v);
           curl_close($v);
       }
       curl_multi_close($this->handle);
     }
     function getCh($url){
        $curl=new curl();
        $curl->setUrl($url);
        $curl->setTimeout();
        $curl->setLocation();
        $curl->setReferrer();
        $curl->setUserAgent();
        $curl->setEncoding();
        $curl->setMethod();
        $curl->setTransfer();
        $curl->setSSL();
        $curl->setHeader0();
        return  $curl->getResultCh();
        //    $this->setTransfer();
        //   $this->setProxy();
        //   $this->setPost();
        // $this->setSSL();
        //  $this->setopt(CURLOPT_HEADER, $this->_header);
    }
}