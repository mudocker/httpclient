<?php

namespace mdocker\lib\curl;


use mdocker\lib\curl\multi\loop;

class ObjectData{
    public $header=0;                                                                                                   //头文件信息作数据流输出
    public $headers = array();
    public $result;
    public $ch;
    const mok=CURLM_OK;
    const perform=CURLM_CALL_MULTI_PERFORM;
    use tcreate_ch;
    use loop;
    function setopt($option,$value,$nullRet=true){
        if (null===$value&&true===$nullRet)return;
        return curl_setopt ($this->ch, $option, $value);
    }

    function setHeader($key, $value){
        $this->headers[$key] = $value;
        $headers = array();
        foreach ($this->headers as $key => $value) $headers[] = $key . ': ' . $value;
        $this->setopt(CURLOPT_HTTPHEADER, $headers);
    }

}
