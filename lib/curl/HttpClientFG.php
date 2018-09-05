<?php
namespace mdocker\lib\curl;


class HttpClientFG
{
    public $buffer = null;
    public $referer = null;
    public $response = null;
    public $request = null;
    private $args = null;

    public static function init(&$instanceof, $args = array()) {
        return $instanceof = new self($args);
    }

    function __construct($args = array()) {
        !is_array($args) and  $args = array();
        $this->args = $args;
        if(empty($this->args['debugging'])) return;
        ob_end_clean();
        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
    }

    function fGet($url, $data = null, $cookie = null) {
        $parse = parse_url($url);
        $url .= isset($parse['query']) ? '&'. $data : ( $data ? '?'. $data : '' );
        $host = $parse['host'];
        $header  = 'Host: '. $host. "\r\n";
        $header .= 'Connection: close'. "\r\n";
        $header .= 'Accept: */*'. "\r\n";
        $header .= 'User-Agent: '. ( isset($this->args['userAgent']) ? $this->args['userAgent'] : $_SERVER['HTTP_USER_AGENT'] ). "\r\n";
        $header .= 'DNT: 1'. "\r\n";
        $cookie and  $header .= 'Cookie: '. $cookie. "\r\n";
        $this->referer and  $header .= 'Referer: '. $this->referer. "\r\n";
        $options = array();
        $options['http']['method'] = 'GET';
        $options['http']['header'] = $header;
        $response = get_headers($url);
        $this->request = $header;
        $this->response = implode("\r\n", $response);
        $context = stream_context_create($options);
        return $this->buffer = file_get_contents($url, false, $context);
    }

    function fPost($url, $data = null, $cookie = null) {

        $parse = parse_url($url);
        $host = $parse['host'];
        $header  = 'Host: '. $host. "\r\n";
        $header .= 'Connection: close'. "\r\n";
        $header .= 'Accept: */*'. "\r\n";
        $header .= 'User-Agent: '. ( isset($this->args['userAgent']) ? $this->args['userAgent'] : $_SERVER['HTTP_USER_AGENT'] ). "\r\n";
        $header .= 'Content-Type: application/x-www-form-urlencoded'. "\r\n";
        $header .= 'DNT: 1'. "\r\n";
        $cookie and  $header .= 'Cookie: '. $cookie. "\r\n";
        $this->referer and  $header .= 'Referer: '. $this->referer. "\r\n";
        $data and  $header .= 'Content-Length: '. strlen($data). "\r\n";
        $options = array();
        $options['http']['method'] = 'POST';
        $options['http']['header'] = $header;
        $data and  $options['http']['content'] = $data;
        $response = get_headers($url);
        $this->request = $header;
        $this->response = implode("\r\n", $response);
        $context = stream_context_create($options);
        return $this->buffer = file_get_contents($url, false, $context);
    }
}