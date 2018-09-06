<?php
/**
 * Created by IntelliJ IDEA.
 * User: ACER-VERITON
 * Date: 2018/9/5
 * Time: 20:06
 */

namespace mdocker\lib\curl\multi;




class curl{

    public $_url;

    //post
    public $_ispost=false;
    public $_method='GET';
    public $_post_data;

//proxy
    public $_proxy;
    public $_username;
    public $_pwd;
    public $_port;
    public $_auth_method;

    public $_transfer;
    public $_encoding;


    public $_location;                                                                                                //重定向
    public $_max_redirs=5;                                                                                             //   查重定次数，防查太深


    public $_user_agent;
    public $_referrer;

    //ssl
    public $_ssl=false;
    public $_ssl_crt;
    public $_ssl_key;


    public $_timeout;

    public $_ch;
    public $_header=0;                                                                                                   //头文件信息作数据流输出
    public $_headers = array();

    function __construct(){
        $this->_ch = curl_init();
    }

    function setUrl($_url){
        $this->_url=$_url;
        $this->setopt(CURLOPT_URL, $this->_url);
    }
    function setPost($ispost=null,$data=array()){
        if (null===$ispost)return
            $this->_post_data=$data;
        $this->setopt(CURLOPT_POST,$this->_ispost);
        $this->setopt(CURLOPT_POSTFIELDS, http_build_query($this->_post_data));
    }
    function setUserAgent($_user_agent="Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)"){
        $this->_user_agent=$_user_agent;
        $this->setopt(CURLOPT_USERAGENT,  $this->_user_agent);
    }
    function setReferrer($_referrer='http://www.baidu.com/search/spider.html'){
        $this->_referrer=$_referrer;
        $this->setopt(CURLOPT_REFERER,  $this->_referrer);
    }
    function setEncoding($_encoding="gzip"){
        $this->_encoding=$_encoding;
        $this->setopt(CURLOPT_ENCODING,  $this->_encoding);
    }

                                                                                    //1  爬取301 302重定向页面
    function setLocation($location=1,$max_redirs=5){
        $this->_location=$location;
        $this->setopt(CURLOPT_FOLLOWLOCATION,  $this->_location);
        $this->_max_redirs=$max_redirs;
        $this->setopt(CURLOPT_MAXREDIRS, $this->_max_redirs);
    }
    function setMethod($_method='GET'){
        $this->_method=$_method;
        $this->setopt(CURLOPT_CUSTOMREQUEST, $this->_method);
    }
    function isHttps(){
        $pos= strpos($this->_url,"https://");
       return false!==$pos;

    }
    function setSSL($ssl=null,$ssl_crt="ssl_crt.crt",$ssl_key="ssl_key.key"){
       if ($this->isHttps()) {
           if (null===$ssl) $this->_ssl=false;
           else   $this->_ssl=$ssl;
       } else return;
        $result=  $this->setopt(CURLOPT_SSL_VERIFYPEER, $this->_ssl);
        $result= $this->setopt(CURLOPT_SSL_VERIFYHOST, $this->_ssl);
        if (false===$this->_ssl)return;
        $this->_ssl_crt=$ssl_crt;
        $this->_ssl_key=$ssl_key;
        if (!file_exists($this->_ssl_crt)||!file_exists($this->_ssl_key))return false;
        $result= $this->setopt(CURLOPT_SSLCERT, $this->_ssl_crt);
        $result= $this->setopt(CURLOPT_SSLKEY, $this->_ssl_key);
    }
    function setTimeout($_timeout=20){
        $this->_timeout=$_timeout;
        $this->setopt(CURLOPT_TIMEOUT, $this->_timeout);
    }
                                                                                         //1文件流形式返回  0直接输出
    function setTransfer($transfer=1){
        $this->_transfer=$transfer;
        $this->setopt(CURLOPT_RETURNTRANSFER,   $this->_transfer);
    }
    function setProxy($proxy=null,$port=null,$username=null,$pwd=null,$auth_method=CURLAUTH_BASIC){
        $this->_proxy=$proxy;
        $this->_username=$port;
        $this->_username=$username;
        $this->_pwd=$pwd;
        $this->_auth_method=$auth_method;
        $this->setopt( CURLOPT_PROXYAUTH, $this->_auth_method);
        $this->setopt(CURLOPT_PROXY, $this->_proxy);
        $this->setopt(CURLOPT_PROXYPORT, $this->_port);
        (null  !== $this->_username && null  !==$this->_pwd ) and  $this->setopt(CURLOPT_PROXYUSERPWD, $this->_username . ':' . $this->_pwd);
    }
    function setHeader($key, $value){
        $this->_headers[$key] = $value;
        $headers = array();
        foreach ($this->_headers as $key => $value) $headers[] = $key . ': ' . $value;
        $this->setopt(CURLOPT_HTTPHEADER, $headers);
    }

    function setopt($option,$value,$nullRet=true){
        if (null===$value&&true===$nullRet)return;
        $result=curl_setopt ($this->_ch, $option, $value);
        return $result;
    }

    function getResultCh(){
       return $this->_ch;
    }
    function setHeader0(){
        $this->setopt(CURLOPT_HEADER, 0);
    }

}