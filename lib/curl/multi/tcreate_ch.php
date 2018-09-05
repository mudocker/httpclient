<?php
/**
 * Created by IntelliJ IDEA.
 * User: ACER-VERITON
 * Date: 2018/9/5
 * Time: 19:31
 */

namespace mdocker\lib\curl;


trait tcreate_ch
{

    function create_ch($url) {
        $this->ch = curl_init();
        $this->setopt(CURLOPT_URL, $url);
        $this->setUserAgent();
        $this->setReferrer();
        $this->setEncoding();
        $this->setReferrer();
        $this->setLocation();
        $this->setMaxRedirs();
        $this->setTimeout();
        $this->setMethod();
        $this->setTransfer();
        $this->setProxy();
        $this->setPost();
        $this->setSSL();
        $this->setopt(CURLOPT_HEADER, $this->header);
        return $this->ch;
    }
    public $user_agent="Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)";
    function setUserAgent(){
        $this->setopt(CURLOPT_USERAGENT,  $this->user_agent);
    }

    public $referrer="http://www.baidu.com/search/spider.html";
    function setReferrer(){
        $this->setopt(CURLOPT_REFERER,  $this->referrer);
    }

    public $encoding="gzip";
    function setEncoding(){
        $this->setopt(CURLOPT_ENCODING,  $this->encoding);
    }

    public $follow_location=1;                                                                                          //1  爬取301 302重定向页面
    function setLocation(){
        $this->setopt(CURLOPT_FOLLOWLOCATION,  $this->follow_location);
    }
    public $max_redirs=5;                                                                                               //   查重定次数，防查太深
    function setMaxRedirs(){
        $this->setopt(CURLOPT_MAXREDIRS, $this->max_redirs);
    }

    function setMethod(){
        $this->setopt(CURLOPT_CUSTOMREQUEST, $this->method);
    }


    public $ispost=false;
    public $method='GET';
    public $post_data;
    function setPost(){
        $this->setopt(CURLOPT_POST,$this->ispost);
        $this->setopt(CURLOPT_POSTFIELDS, http_build_query($this->post_data));
    }

    public $ssl=false;
    public $ssl_crt;
    public $ssl_key;
    function setSSL(){
        $this->setopt(CURLOPT_SSL_VERIFYPEER, $this->ssl);
        $this->setopt(CURLOPT_SSL_VERIFYHOST, $this->ssl);
        if ($this->ssl!=true)return;
        if (!file_exists(ssl_crt)||!file_exists(ssl_key))return false;
        $this->setopt(CURLOPT_SSLCERT, $this->ssl_crt);
        $this->setopt(CURLOPT_SSLKEY, $this->ssl_key);
    }




    public $timeout=20;
    function setTimeout(){
        $this->setopt(CURLOPT_TIMEOUT, $this->timeout);
    }


    public $return_transfer=1;                                                                                          //1文件流形式返回  0直接输出
    function setTransfer(){
        $this->setopt(CURLOPT_RETURNTRANSFER,   $this->return_transfer);
    }


    public $proxy;
    public $username;
    public $pwd;
    public $port;
    function setProxy(){
        $this->setopt(CURLOPT_PROXY, $this->proxy);
        $this->setopt(CURLOPT_PROXYPORT, $this->port);
        (null  !== $this->username && null  !==$this->pwd ) and  $this->setopt(CURLOPT_PROXYUSERPWD, $this->username . ':' . $this->pwd);
    }

}