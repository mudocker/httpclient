<?php

namespace mdocker\lib\curl;



class Curl
{
    const VERSION = '8.3.1';
    const DEFAULT_TIMEOUT = 30;

    public $curl;
    public $id = null;

    public $error = false;
    public $errorCode = 0;
    public $errorMessage = null;

    public $curlError = false;
    public $curlErrorCode = 0;
    public $curlErrorMessage = null;

    public $httpError = false;
    public $httpStatusCode = 0;
    public $httpErrorMessage = null;

    public $url = null;
    public $requestHeaders = null;
    public $responseHeaders = null;
    public $rawResponseHeaders = '';
    public $responseCookies = array();
    public $response = null;
    public $rawResponse = null;

    public $beforeSendCallback = null;
    public $downloadCompleteCallback = null;
    public $successCallback = null;
    public $errorCallback = null;
    public $completeCallback = null;
    public $fileHandle = null;

    public $attempts = 0;
    public $retries = 0;
    public $childOfMultiCurl = false;
    public $remainingRetries = 0;
    public $retryDecider = null;

    public $jsonDecoder = null;
    public $xmlDecoder = null;

    private $cookies = array();
    private $headers = array();
    private $options = array();

    private $jsonDecoderArgs = array();
    private $jsonPattern = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
    private $xmlDecoderArgs = array();
    private $xmlPattern = '~^(?:text/|application/(?:atom\+|rss\+|soap\+)?)xml~i';
    private $defaultDecoder = null;

    public static $RFC2616 = array(
        // RFC 2616: "any CHAR except CTLs or separators".
        // CHAR           = <any US-ASCII character (octets 0 - 127)>
        // CTL            = <any US-ASCII control character
        //                  (octets 0 - 31) and DEL (127)>
        // separators     = "(" | ")" | "<" | ">" | "@"
        //                | "," | ";" | ":" | "\" | <">
        //                | "/" | "[" | "]" | "?" | "="
        //                | "{" | "}" | SP | HT
        // SP             = <US-ASCII SP, space (32)>
        // HT             = <US-ASCII HT, horizontal-tab (9)>
        // <">            = <US-ASCII double-quote mark (34)>
        '!', '#', '$', '%', '&', "'", '*', '+', '-', '.', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B',
        'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q',
        'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '|', '~',
    );
    public static $RFC6265 = array(
        // RFC 6265: "US-ASCII characters excluding CTLs, whitespace DQUOTE, comma, semicolon, and backslash".
        // %x21
        '!',
        // %x23-2B
        '#', '$', '%', '&', "'", '(', ')', '*', '+',
        // %x2D-3A
        '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':',
        // %x3C-5B
        '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[',
        // %x5D-7E
        ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~',
    );

    private static $deferredProperties = array(
        'effectiveUrl',
        'rfc2616',
        'rfc6265',
        'totalTime',
    );

    function __construct($base_url = null){
       !extension_loaded('curl') and  error_exception('cURL library is not loaded');
        $this->curl = curl_init();
        $this->initialize($base_url);
    }

    function beforeSend($callback){
        $this->beforeSendCallback = $callback;
    }

    function buildPostData($data)
    {
        $binary_data = false;
        if ($this->isJson($data)) $data = Encoder::encodeJson($data);

        elseif (is_array($data)) $this->postDataArray($data,$binary_data);


        (!$binary_data && (is_array($data) || is_object($data))) and  $data = http_build_query($data, '', '&');

        return $data;
    }

    private function isJson(&$data){
   return  isset($this->headers['Content-Type'])
         && preg_match($this->jsonPattern, $this->headers['Content-Type'])
         && (is_array($data)
         || (is_object($data)
         && interface_exists('JsonSerializable', false)
         && $data instanceof \JsonSerializable));
    }

    function postDataArray(&$data,&$binary_data){
        ArrayUtil::is_array_multidim($data) and  $data = ArrayUtil::flatten_multidim($data);
        foreach ($data as $key => $value) {
            if (is_string($value) && strpos($value, '@') === 0 && is_file(substr($value, 1)))                               {$binary_data = true;class_exists('CURLFile') and  $data[$key] = new \CURLFile(substr($value, 1));
            } elseif ($value instanceof \CURLFile)                                                                      $binary_data = true;
        }
    }

   function call(){
     $args = func_get_args();
     $function = array_shift($args);
     if (!is_callable($function)) return;
     array_unshift($args, $this);
     call_user_func_array($function, $args);

    }

    function close(){
        is_resource($this->curl) and  curl_close($this->curl);
        $this->options = null;
        $this->jsonDecoder = null;
        $this->jsonDecoderArgs = null;
        $this->xmlDecoder = null;
        $this->xmlDecoderArgs = null;
        $this->defaultDecoder = null;
    }


    function complete($callback){
        $this->completeCallback = $callback;
    }

   function progress($callback){
        $this->setOpt(CURLOPT_PROGRESSFUNCTION, $callback);
        $this->setOpt(CURLOPT_NOPROGRESS, false);
    }

   function delete($url, $query_parameters = array(), $data = array())
    {
        if (is_array($url)) {
            $data = $query_parameters;
            $query_parameters = $url;
            $url = (string)$this->url;
        }

        $this->setUrl($url, $query_parameters);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

   function download($url, $mixed_filename)
    {
        if (is_callable($mixed_filename)) {
            $this->downloadCompleteCallback = $mixed_filename;
            $this->fileHandle = tmpfile();
        } else {
            $filename = $mixed_filename;
            $download_filename = $filename . '.pccdownload';

            $mode = 'wb';
            if (file_exists($download_filename) && $filesize = filesize($download_filename)) {
                $mode = 'ab';
                $first_byte_position = $filesize;
                $range = $first_byte_position . '-';
                $this->setOpt(CURLOPT_RANGE, $range);
            }
            $this->fileHandle = fopen($download_filename, $mode);


            $this->downloadCompleteCallback = function ($instance, $fh) use ($download_filename, $filename) {
               is_resource($fh) and  fclose($fh);
                rename($download_filename, $filename);
            };
        }
        $this->setOpt(CURLOPT_FILE, $this->fileHandle);
        $this->get($url);
        return ! $this->error;
    }
     function error($callback){
        $this->errorCallback = $callback;
    }

    function exec($ch = null){
        $this->attempts += 1;
        $this->jsonDecoder === null                                                                                   and    $this->setDefaultJsonDecoder();
        $this->xmlDecoder === null                                                                                     and $this->setDefaultXmlDecoder();
        if ($ch === null) {
            $this->responseCookies = array();
            $this->call($this->beforeSendCallback);
            $this->rawResponse = curl_exec($this->curl);
            $this->curlErrorCode = curl_errno($this->curl);
            $this->curlErrorMessage = curl_error($this->curl);
        } else {
            $this->rawResponse = curl_multi_getcontent($ch);
            $this->curlErrorMessage = curl_error($ch);
        }
        $this->curlError = !($this->curlErrorCode === 0);
        $this->rawResponseHeaders = $this->headerCallbackData->rawResponseHeaders;
        $this->responseCookies = $this->headerCallbackData->responseCookies;
        $this->headerCallbackData->rawResponseHeaders = '';
        $this->headerCallbackData->responseCookies = array();
       $this->curlError && function_exists('curl_strerror') and  $this->curlErrorMessage = curl_strerror($this->curlErrorCode) . (empty($this->curlErrorMessage) ? '' : ': ' . $this->curlErrorMessage);
        $this->httpStatusCode = $this->getInfo(CURLINFO_HTTP_CODE);
        $this->httpError = in_array(floor($this->httpStatusCode / 100), array(4, 5));
        $this->error = $this->curlError || $this->httpError;
        $this->errorCode = $this->error ? ($this->curlError ? $this->curlErrorCode : $this->httpStatusCode) : 0;
        $this->getOpt(CURLINFO_HEADER_OUT) === true and  $this->requestHeaders = $this->parseRequestHeaders($this->getInfo(CURLINFO_HEADER_OUT));
        $this->responseHeaders = $this->parseResponseHeaders($this->rawResponseHeaders);
        $this->response = $this->parseResponse($this->responseHeaders, $this->rawResponse);
        $this->httpErrorMessage = '';
        if ($this->error) isset($this->responseHeaders['Status-Line']) and  $this->httpErrorMessage = $this->responseHeaders['Status-Line'];
        $this->errorMessage = $this->curlError ? $this->curlErrorMessage : $this->httpErrorMessage;
        unset($this->effectiveUrl);
        unset($this->totalTime);
        $this->unsetHeader('Content-Length');
        $this->setOpt(CURLOPT_NOBODY, false);
        if ($this->isChildOfMultiCurl()) return;
        if ($this->attemptRetry()) return $this->exec($ch);
        $this->execDone();
        return $this->response;
    }

     function execDone(){
        $this->call( $this->error? $this->errorCallback:$this->successCallback);
        $this->call($this->completeCallback);
        !($this->fileHandle === null) and  $this->downloadComplete($this->fileHandle);
    }

   function get($url, $data = array()){
       $this->is_array_string($data,$url);
        $this->setUrl($url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

     function getInfo($opt = null){
        $args = array();
        $args[] = $this->curl;
        func_num_args() and   $args[] = $opt;
        return call_user_func_array('curl_getinfo', $args);
    }

     function getOpt($option){
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    function head($url, $data = array()){
        $this->is_array_string($data,$url);
        $this->setUrl($url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
        $this->setOpt(CURLOPT_NOBODY, true);
        return $this->exec();
    }

     function options($url, $data = array()){
         $this->is_array_string($data,$url);
        $this->setUrl($url, $data);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        return $this->exec();
    }

    function is_array_string(&$data,&$url){
        if (!is_array($url)) return;
        $data = $url;
        $url = (string)$this->url;
    }
   function patch($url, $data = array()){
        $this->is_array_string($data,$url);
        is_array($data) && empty($data) and  $this->removeHeader('Content-Length');
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

    function post($url, $data = '', $follow_303_with_post = false)
    {
        if (is_array($url)) {
            $follow_303_with_post = (bool)$data;
            $data = $url;
            $url = (string)$this->url;
        }
        $this->setUrl($url);
        if ($follow_303_with_post) $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
         else {
            if (isset($this->options[CURLOPT_CUSTOMREQUEST])) {
                if ((version_compare(PHP_VERSION, '5.5.11') < 0) || defined('HHVM_VERSION')) trigger_error('Due to technical limitations of PHP <= 5.5.11 and HHVM, it is not possible to ' . 'perform a post-redirect-get request using a php-curl-class Curl object that ' . 'has already been used to perform other types of requests. Either use a new ' . 'php-curl-class Curl object or upgrade your PHP engine.', E_USER_ERROR);
                else $this->setOpt(CURLOPT_CUSTOMREQUEST, null);
            }
        }

        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->buildPostData($data));
        return $this->exec();
    }

  function put($url, $data = array()){
        if (is_array($url)) {
            $data = $url;
            $url = (string)$this->url;
        }
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $put_data = $this->buildPostData($data);
        if (empty($this->options[CURLOPT_INFILE]) && empty($this->options[CURLOPT_INFILESIZE])) is_string($put_data) and  $this->setHeader('Content-Length', strlen($put_data));
       !empty($put_data) and  $this->setOpt(CURLOPT_POSTFIELDS, $put_data);

        return $this->exec();
    }

     function search($url, $data = array()){
        if (is_array($url)) {
            $data = $url;
            $url = (string)$this->url;
        }
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'SEARCH');
        $put_data = $this->buildPostData($data);
        if (empty($this->options[CURLOPT_INFILE]) && empty($this->options[CURLOPT_INFILESIZE])) is_string($put_data) and  $this->setHeader('Content-Length', strlen($put_data));
        !empty($put_data) and  $this->setOpt(CURLOPT_POSTFIELDS, $put_data);

        return $this->exec();
    }
    function setBasicAuthentication($username, $password = ''){
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    function setDigestAuthentication($username, $password = ''){
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

   function setCookie($key, $value){
        $this->setEncodedCookie($key, $value);
        $this->buildCookies();
    }

  function setCookies($cookies){
        foreach ($cookies as $key => $value) $this->setEncodedCookie($key, $value);
        $this->buildCookies();
    }

   function getCookie($key){
        return $this->getResponseCookie($key);
    }

    function getResponseCookie($key){
        return isset($this->responseCookies[$key]) ? $this->responseCookies[$key] : null;
    }

   function setMaxFilesize($bytes){
        $gte_v550 = version_compare(PHP_VERSION, '5.5.0') >= 0;
        $callback =  $gte_v550?  function ($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($bytes) {return $downloaded > $bytes ? 1 : 0;}: function ($download_size, $downloaded, $upload_size, $uploaded) use ($bytes) {return $downloaded > $bytes ? 1 : 0;};
        $this->progress($callback);
    }

    function setPort($port){
        $this->setOpt(CURLOPT_PORT, intval($port));
    }

    function setConnectTimeout($seconds=10){
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $seconds);
    }

   function setCookieString($string){
        return $this->setOpt(CURLOPT_COOKIE, $string);
    }

    function setCookieFile($cookie_file){
        return $this->setOpt(CURLOPT_COOKIEFILE, $cookie_file);
    }

   function setCookieJar($cookie_jar){
        return $this->setOpt(CURLOPT_COOKIEJAR, $cookie_jar);
    }

    function setDefaultJsonDecoder()
    {
        $this->jsonDecoder = '\mdocker\lib\curl\Decoder::decodeJson';
        $this->jsonDecoderArgs = func_get_args();
    }

    function setDefaultXmlDecoder(){
        $this->xmlDecoder = '\mdocker\lib\curl\Decoder::decodeXml';
        $this->xmlDecoderArgs = func_get_args();
    }

    function setDefaultDecoder($mixed = 'json')
    {
        if ($mixed === false)                                                                                           $this->defaultDecoder = false;
         elseif (is_callable($mixed))                                                                                   $this->defaultDecoder = $mixed;
         else {
              $mixed === 'json'                                                                                        and  $this->defaultDecoder = '\mdocker\lib\curl\Decoder::decodeJson';
              $mixed === 'xml'                                                                                         and  $this->defaultDecoder = '\mdocker\lib\curl\Decoder::decodeXml';
        }
    }

    function setDefaultTimeout(){
        $this->setTimeout(self::DEFAULT_TIMEOUT);
    }

   function setDefaultUserAgent(){
        $user_agent = 'PHP-Curl-Class/' . self::VERSION . ' (+https://github.com/php-curl-class/php-curl-class)';
        $user_agent .= ' PHP/' . PHP_VERSION;
        $curl_version = curl_version();
        $user_agent .= ' curl/' . $curl_version['version'];
        $this->setUserAgent($user_agent);
    }

     function setHeader($key, $value){
    $this->headers[$key] = $value;
    $headers = array();
    foreach ($this->headers as $key => $value) $headers[] = $key . ': ' . $value;
    $this->setOpt(CURLOPT_HTTPHEADER, $headers);
}

   function setHeaders($headers){
        foreach ($headers as $key => $value) $this->headers[$key] = $value;
        $headers = array();
        foreach ($this->headers as $key => $value) $headers[] = $key . ': ' . $value;
        $this->setOpt(CURLOPT_HTTPHEADER, $headers);
    }

    function setJsonDecoder($mixed){


        if ($mixed !== false&& !is_callable($mixed)) return;
            $this->jsonDecoder = $mixed;
            $this->jsonDecoderArgs = array();

    }

    function setXmlDecoder($mixed){
        if ($mixed!== false && !is_callable($mixed)) return;
        $this->xmlDecoder = $mixed;
        $this->xmlDecoderArgs = array();

    }

   function setOpt($option, $value){
        $required_options = array(CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER',);
        (in_array($option, array_keys($required_options), true) && !($value === true))                                 and  trigger_error($required_options[$option] . ' is a required option', E_USER_WARNING);
        $success = curl_setopt($this->curl, $option, $value);
        $success                                                                                                        and    $this->options[$option] = $value;
        return $success;
    }

   function setOpts($options){
        foreach ($options as $option => $value) if (!$this->setOpt($option, $value)) return false;
        return true;
    }

   function setProxy($proxy, $port = null, $username = null, $password = null){
       $this->setOpt(CURLOPT_PROXY, $proxy);
       $port !== null                                                                                                     and  $this->setOpt(CURLOPT_PROXYPORT, $port);
      ($username !== null && $password !== null)                                                                           and  $this->setOpt(CURLOPT_PROXYUSERPWD, $username . ':' . $password);

    }

      function setProxyAuth($auth){
        $this-setOpt(CURLOPT_PROXYAUTH, $auth);
    }

   function setProxyType($type){
        $this->setOpt(CURLOPT_PROXYTYPE, $type);
    }

   function setProxyTunnel($tunnel = true){
        $this->setOpt(CURLOPT_HTTPPROXYTUNNEL, $tunnel);
    }

   function unsetProxy(){
        $this->setOpt(CURLOPT_PROXY, null);
    }

   function setReferer($referer){
        $this->setReferrer($referer);
    }

  function setReferrer($referrer){
        $this->setOpt(CURLOPT_REFERER, $referrer);
    }

   function setRetry($mixed)
    {
        if (is_callable($mixed)) $this->retryDecider = $mixed;
         elseif (is_int($mixed)) {
            $maximum_number_of_retries = $mixed;
            $this->remainingRetries = $maximum_number_of_retries;
        }
    }

     function setTimeout($seconds){
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

    function setUrl($url, $mixed_data = ''){
        $built_url = $this->buildUrl($url, $mixed_data);
        $this->url = $this->url === null?    (string)new Url($built_url): (string)new Url($this->url, $built_url);
        $this->setOpt(CURLOPT_URL, $this->url);
    }

     function setUserAgent($user_agent){
        $this->setOpt(CURLOPT_USERAGENT, $user_agent);
    }

     function attemptRetry()
    {
        $attempt_retry = false;
        if (!$this->error)   return $attempt_retry;
        $attempt_retry =  $this->retryDecider === null?  $this->remainingRetries >= 1:call_user_func($this->retryDecider, $this);
        if (!$attempt_retry)  return $attempt_retry;
         $this->retries += 1;
         $this->remainingRetries and  $this->remainingRetries -= 1;
        return $attempt_retry;
    }

    function success($callback){
        $this->successCallback = $callback;
    }

     function unsetHeader($key){
        unset($this->headers[$key]);
        $headers = array();
        foreach ($this->headers as $key => $value) $headers[] = $key . ': ' . $value;
        $this->setOpt(CURLOPT_HTTPHEADER, $headers);
    }
     function removeHeader($key){
        $this->setHeader($key, '');
    }

     function verbose($on = true, $output = STDERR){
       $on and  $this->setOpt(CURLINFO_HEADER_OUT, false);
        $this->setOpt(CURLOPT_VERBOSE, $on);
        $this->setOpt(CURLOPT_STDERR, $output);
    }

    function reset(){
        function_exists('curl_reset') && is_resource($this->curl)? curl_reset($this->curl):$this->curl = curl_init();
        $this->initialize();
    }
     function getCurl(){
        return $this->curl;
    }
     function getId(){
        return $this->id;
    }
     function isError(){
        return $this->error;
    }
     function getErrorCode(){
        return $this->errorCode;
    }

    function getErrorMessage(){
        return $this->errorMessage;
    }
     function isCurlError(){
        return $this->curlError;
    }
     function getCurlErrorCode(){
        return $this->curlErrorCode;
    }
    function getCurlErrorMessage(){
        return $this->curlErrorMessage;
    }
    function isHttpError(){
        return $this->httpError;
    }
     function getHttpStatusCode(){
        return $this->httpStatusCode;
    }
     function getHttpErrorMessage(){
        return $this->httpErrorMessage;
    }
     function getUrl(){
        return $this->url;
    }
     function getRequestHeaders(){
        return $this->requestHeaders;
    }
     function getResponseHeaders(){
        return $this->responseHeaders;
    }
     function getRawResponseHeaders(){
        return $this->rawResponseHeaders;
    }
     function getResponseCookies(){
        return $this->responseCookies;
    }
     function getResponse(){
        return $this->response;
    }
    function getRawResponse(){
        return $this->rawResponse;
    }
     function getBeforeSendCallback(){
        return $this->beforeSendCallback;
    }
     function getDownloadCompleteCallback(){
        return $this->downloadCompleteCallback;
    }
     function getSuccessCallback(){
        return $this->successCallback;
    }
     function getErrorCallback(){
        return $this->errorCallback;
    }
    function getCompleteCallback(){
        return $this->completeCallback;
    }
     function getFileHandle(){
        return $this->fileHandle;
    }
    function getAttempts(){
        return $this->attempts;
    }
     function getRetries(){
        return $this->retries;
    }
    function isChildOfMultiCurl(){
        return $this->childOfMultiCurl;
    }
    function getRemainingRetries(){
        return $this->remainingRetries;
    }
     function getRetryDecider(){
        return $this->retryDecider;
    }
     function getJsonDecoder(){
        return $this->jsonDecoder;
    }
     function getXmlDecoder(){
        return $this->xmlDecoder;
    }

   function __destruct(){
        $this->close();
    }
     function __get($name){
        $return = null;
        in_array($name, self::$deferredProperties) && is_callable(array($this, $getter = '__get_' . $name)) and  $return = $this->$name = $this->$getter();
        return $return;
    }

    private function __get_effectiveUrl(){
        return $this->getInfo(CURLINFO_EFFECTIVE_URL);
    }
    private function __get_rfc2616(){
        return array_fill_keys(self::$RFC2616, true);
    }
    private function __get_rfc6265(){
        return array_fill_keys(self::$RFC6265, true);
    }
    private function __get_totalTime(){
        return $this->getInfo(CURLINFO_TOTAL_TIME);
    }


    private function buildCookies(){
        $this->setOpt(CURLOPT_COOKIE, implode('; ', array_map(function ($k, $v) {return $k . '=' . $v;}, array_keys($this->cookies), array_values($this->cookies))));
    }


    private function buildUrl($url, $mixed_data = ''){
        $query_string = '';
        if (!empty($mixed_data)) {
            $query_mark = strpos($url, '?') > 0 ? '&' : '?';
           is_string($mixed_data)                                                                                       and        $query_string .= $query_mark . $mixed_data;
           is_array($mixed_data)                                                                                        and        $query_string .= $query_mark . http_build_query($mixed_data, '', '&');
        }
        return $url . $query_string;
    }


    private function downloadComplete($fh){
        if (!$this->error && $this->downloadCompleteCallback) {
            rewind($fh);
            $this->call($this->downloadCompleteCallback, $fh);
            $this->downloadCompleteCallback = null;
        }
        is_resource($fh) and  fclose($fh);
        !defined('STDOUT') and  define('STDOUT', fopen('php://stdout', 'w'));
        $this->setOpt(CURLOPT_FILE, STDOUT);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
    }


    private function parseHeaders($raw_headers){
        $raw_headers = preg_split('/\r\n/', $raw_headers, null, PREG_SPLIT_NO_EMPTY);
        $http_headers = new CaseInsensitiveArray();
        $raw_headers_count = count($raw_headers);
        for ($i = 1; $i < $raw_headers_count; $i++) {
            if (strpos($raw_headers[$i], ':') !== false) {
                list($key, $value) = explode(':', $raw_headers[$i], 2);
                $key = trim($key);
                $value = trim($value);
                isset($http_headers[$key])? $http_headers[$key] .= ',' . $value:$http_headers[$key] = $value;
            }
        }

        return array(isset($raw_headers['0']) ? $raw_headers['0'] : '', $http_headers);
    }

    private function parseRequestHeaders($raw_headers){
        $request_headers = new CaseInsensitiveArray();
        list($first_line, $headers) = $this->parseHeaders($raw_headers);
        $request_headers['Request-Line'] = $first_line;
        foreach ($headers as $key => $value) $request_headers[$key] = $value;
        return $request_headers;
    }


    private function parseResponse($response_headers, $raw_response)
    {
        $response = $raw_response;
        if (isset($response_headers['Content-Type'])) {
            if (preg_match($this->jsonPattern, $response_headers['Content-Type'])) {
                if ($this->jsonDecoder) {
                    $args = $this->jsonDecoderArgs;
                    array_unshift($args, $response);
                    $response = call_user_func_array($this->jsonDecoder, $args);
                }
            } elseif (preg_match($this->xmlPattern, $response_headers['Content-Type'])) {
                if ($this->xmlDecoder) {
                    $args = $this->xmlDecoderArgs;
                    array_unshift($args, $response);
                    $response = call_user_func_array($this->xmlDecoder, $args);
                }
            } else $this->defaultDecoder and  $response = call_user_func($this->defaultDecoder, $response);
        }
        return $response;
    }


    private function parseResponseHeaders($raw_response_headers)
    {
        $response_header_array = explode("\r\n\r\n", $raw_response_headers);
        $response_header  = '';
        for ($i = count($response_header_array) - 1; $i >= 0; $i--) {
            if (stripos($response_header_array[$i], 'HTTP/') === 0) {
                $response_header = $response_header_array[$i];
                break;
            }
        }

        $response_headers = new CaseInsensitiveArray();
        list($first_line, $headers) = $this->parseHeaders($response_header);
        $response_headers['Status-Line'] = $first_line;
        foreach ($headers as $key => $value) $response_headers[$key] = $value;
        return $response_headers;
    }


    private function setEncodedCookie($key, $value){
        $name_chars = array();
        foreach (str_split($key) as $name_char) $name_chars[] =isset($this->rfc2616[$name_char])? $name_char: rawurlencode($name_char);
        $value_chars = array();
        foreach (str_split($value) as $value_char) $value_chars[] = isset($this->rfc6265[$value_char])?  $value_char:rawurlencode($value_char);
        $this->cookies[implode('', $name_chars)] = implode('', $value_chars);
    }


    private function initialize($base_url = null)
    {
        $this->id = uniqid('', true);
        $this->setDefaultUserAgent();
        $this->setDefaultTimeout();
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        $data = new \stdClass();
        $data->rawResponseHeaders = '';
        $data->responseCookies = array();
        $this->headerCallbackData = $data;
        $this->setOpt(CURLOPT_HEADERFUNCTION, createHeaderCallback($data));
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->headers = new CaseInsensitiveArray();
        $this->setUrl($base_url);
    }
}

