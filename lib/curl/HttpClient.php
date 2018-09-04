<?php

namespace mdocker\lib\curl;

class HttpClient
{
    public $baseUrl = null;
    public $multiCurl;

    private $curls = array();
    private $activeCurls = array();
    private $isStarted = false;
    private $concurrency = 25;
    private $nextCurlId = 0;

    private $beforeSendCallback = null;
    private $successCallback = null;
    private $errorCallback = null;
    private $completeCallback = null;

    private $retry = null;

    private $cookies = array();
    private $headers = array();
    private $options = array();

    private $jsonDecoder = null;
    private $xmlDecoder = null;

   function __construct($base_url = null){
        $this->multiCurl = curl_multi_init();
        $this->headers = new CaseInsensitiveArray();
        $this->setUrl($base_url);
    }


    public function del($url, $query_parameters = array(), $data = array())
    {
        if (is_array($url)) {
            $data = $query_parameters;
            $query_parameters = $url;
            $url = $this->baseUrl;
        }
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url, $query_parameters);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $curl->setOpt(CURLOPT_POSTFIELDS, $curl->buildPostData($data));
        return $curl;
    }
    function download($url, $mixed_filename){
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url);
        if (is_callable($mixed_filename)) {
            $callback = $mixed_filename;
            $curl->downloadCompleteCallback = $callback;
            $curl->fileHandle = tmpfile();
        } else {
            $filename = $mixed_filename;
            $curl->downloadCompleteCallback = function ($instance, $fh) use ($filename) {file_put_contents($filename, stream_get_contents($fh));};
            $curl->fileHandle = fopen('php://temp', 'wb');
        }
        $curl->setOpt(CURLOPT_FILE, $curl->fileHandle);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $curl->setOpt(CURLOPT_HTTPGET, true);
        return $curl;
    }

         function get($url, $data = array()){
        $this->is_array_string($data,$url);
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url, $data);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $curl->setOpt(CURLOPT_HTTPGET, true);
        return $curl;
    }
    function is_array_string(&$data,&$url){
        if (!is_array($url)) return;
        $data = $url;
        $url = (string) $this->baseUrl;
    }
     function header($url, $data = array()){

         $this->is_array_string($data,$url);
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url, $data);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
        $curl->setOpt(CURLOPT_NOBODY, true);
        return $curl;
    }

     function options($url, $data = array()){
        $this->is_array_string($data,$url);
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url, $data);
        $curl->removeHeader('Content-Length');
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        return $curl;
    }

   function patch($url, $data = array()){
        $this->is_array_string($data,$url);
        $curl = new Curl();
       (is_array($data) && empty($data))and  $curl->removeHeader('Content-Length');
        $this->queueHandle($curl);
        $curl->setUrl($url);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $curl->setOpt(CURLOPT_POSTFIELDS, $curl->buildPostData($data));
        return $curl;
    }

     function post($url, $data = '', $follow_303_with_post = false){
        if (is_array($url)) {
            $follow_303_with_post = (bool)$data;
            $data = $url;
            $url = $this->baseUrl;
        }
        $curl = new Curl();
        $this->queueHandle($curl);
       (is_array($data) && empty($data)) and  $curl->removeHeader('Content-Length');
        $curl->setUrl($url);
        !$follow_303_with_post and  $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $curl->setOpt(CURLOPT_POST, true);
        $curl->setOpt(CURLOPT_POSTFIELDS, $curl->buildPostData($data));
        return $curl;
    }

     function put($url, $data = array()){
        $this->is_array_string($data,$url);
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $put_data = $curl->buildPostData($data);
        is_string($put_data) and  $curl->setHeader('Content-Length', strlen($put_data));
        $curl->setOpt(CURLOPT_POSTFIELDS, $put_data);
        return $curl;
    }

    function search($url, $data = array()){
        $this->is_array_string($data,$url);
        $curl = new Curl();
        $this->queueHandle($curl);
        $curl->setUrl($url);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'SEARCH');
        $put_data = $curl->buildPostData($data);
        is_string($put_data) and  $curl->setHeader('Content-Length', strlen($put_data));
        $curl->setOpt(CURLOPT_POSTFIELDS, $put_data);
        return $curl;
    }

    function curl(Curl $curl){
        $this->queueHandle($curl);
        return $curl;
    }

   function beforeSend($callback){
        $this->beforeSendCallback = $callback;
    }

    function close(){
        foreach ($this->curls as $curl) $curl->close();
       is_resource($this->multiCurl) and  curl_multi_close($this->multiCurl);
    }

     function complete($callback){
        $this->completeCallback = $callback;
    }

    function error($callback){
        $this->errorCallback = $callback;
    }

    function getOpt($option){
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    function setBasicAuthentication($username, $password = ''){
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    function setConcurrency($concurrency){
        $this->concurrency = $concurrency;
    }

     function setDigestAuthentication($username, $password = ''){
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    function setCookie($key, $value){
        $this->cookies[$key] = $value;
    }

     function setCookies($cookies){
        foreach ($cookies as $key => $value) $this->cookies[$key] = $value;
    }

    function setPort($port){
        $this->setOpt(CURLOPT_PORT, intval($port));
    }

   function setConnectTimeout($seconds=10){
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $seconds);
    }

    function setCookieString($string){
        $this->setOpt(CURLOPT_COOKIE, $string);
    }

    function setCookieFile($cookie_file){
        $this->setOpt(CURLOPT_COOKIEFILE, $cookie_file);
    }

    function setCookieJar($cookie_jar){
        $this->setOpt(CURLOPT_COOKIEJAR, $cookie_jar);
    }

    function setHeader($key, $value){
        $this->headers[$key] = $value;
        $this->updateHeaders();
    }

    function setHeaders($headers){
        foreach ($headers as $key => $value) $this->headers[$key] = $value;
        $this->updateHeaders();
    }

    function setJsonDecoder($mixed){
        $mixed === false and  $this->jsonDecoder = false;
        is_callable($mixed) and  $this->jsonDecoder = $mixed;
    }

     function setXmlDecoder($mixed){
        $mixed === false and  $this->xmlDecoder = false;
        is_callable($mixed) and  $this->xmlDecoder = $mixed;
    }

    function setOpt($option, $value){
        $this->options[$option] = $value;
    }
    function setOpts($options){
        foreach ($options as $option => $value) $this->setOpt($option, $value);
    }

   function setReferrer($referrer="http://www.baidu.com/search/spider.html"){
        $this->setOpt(CURLOPT_REFERER, $referrer);
    }

    function setRetry($mixed){
        $this->retry = $mixed;
    }

    function setTimeout($seconds=10){
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

   function setUrl($url){
        $this->baseUrl = $url;
    }
                                                                                                                            //http://yusure.cn/php/28.html curl模拟百度蜘蛛进行采集
  function setUserAgent($user_agent="Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)"){
        $this->setOpt(CURLOPT_USERAGENT, $user_agent);
    }

    function start(){
        if ($this->isStarted) return;
        $this->isStarted = true;
        $concurrency = $this->concurrency;
        $concurrency > count($this->curls) and $concurrency = count($this->curls);
        for ($i = 0; $i < $concurrency; $i++) $this->initHandle(array_shift($this->curls));
        do {
              curl_multi_select($this->multiCurl) === -1 and  usleep(100000);
              curl_multi_exec($this->multiCurl, $active);
            while (!($info_array = curl_multi_info_read($this->multiCurl)) === false) {
                if ($info_array['msg'] === CURLMSG_DONE) {
                    foreach ($this->activeCurls as $key => $curl) {
                        if ($curl->curl === $info_array['handle']) {
                            $curl->curlErrorCode = $info_array['result'];
                            $curl->exec($curl->curl);
                            if ($curl->attemptRetry()) {
                                curl_multi_remove_handle($this->multiCurl, $curl->curl);
                                $curlm_error_code = curl_multi_add_handle($this->multiCurl, $curl->curl);
                               !($curlm_error_code === CURLM_OK) and  error_exception ('cURL multi add handle error: ' . curl_multi_strerror($curlm_error_code));
                            } else {
                                $curl->execDone();
                                unset($this->activeCurls[$key]);
                                while (count($this->curls) >= 1 && count($this->activeCurls) < $this->concurrency) $this->initHandle(array_shift($this->curls));
                                curl_multi_remove_handle($this->multiCurl, $curl->curl);
                                $curl->close();
                            }
                            break;
                        }
                    }
                }
            }

           !$active and  $active = count($this->activeCurls);
        } while ($active > 0);
        $this->isStarted = false;
    }

   function success($callback){
        $this->successCallback = $callback;
    }
   function unsetHeader($key){
        unset($this->headers[$key]);
    }
    function removeHeader($key){
        $this->setHeader($key, '');
    }

     function verbose($on = true, $output = STDERR){
         $on and  $this->setOpt(CURLINFO_HEADER_OUT, false);
        $this->setOpt(CURLOPT_VERBOSE, $on);
        $this->setOpt(CURLOPT_STDERR, $output);
    }
    function __destruct(){
        $this->close();
    }

    private function updateHeaders(){
        foreach ($this->curls as $curl) $curl->setHeaders($this->headers);
    }
    private function queueHandle($curl){
        $curl->id = $this->nextCurlId++;
        $curl->childOfMultiCurl = true;
        $this->curls[$curl->id] = $curl;
        $curl->setHeaders($this->headers);
    }


    private function initHandle($curl){
        $curl->beforeSendCallback === null                                                                           and  $curl->beforeSend($this->beforeSendCallback);
        $curl->successCallback === null                                                                              and  $curl->success($this->successCallback);
        $curl->errorCallback === null                                                                                 and $curl->error($this->errorCallback);
        $curl->completeCallback === null                                                                             and $curl->complete($this->completeCallback);
        $curl->jsonDecoder === null                                                                                   and $curl->setJsonDecoder($this->jsonDecoder);
        $curl->xmlDecoder === null                                                                                    and  $curl->setXmlDecoder($this->xmlDecoder);
        $curl->setOpts($this->options);
        $curl->setRetry($this->retry);
        $curl->setCookies($this->cookies);
        $curlm_error_code = curl_multi_add_handle($this->multiCurl, $curl->curl);
        !($curlm_error_code === CURLM_OK)                                                                              and error_exception('cURL multi add handle error: ' . curl_multi_strerror($curlm_error_code));
        $this->activeCurls[$curl->id] = $curl;
        $curl->call($curl->beforeSendCallback);
    }
}
