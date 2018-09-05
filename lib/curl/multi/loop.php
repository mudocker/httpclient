<?php
/**
 * Created by IntelliJ IDEA.
 * User: ACER-VERITON
 * Date: 2018/9/5
 * Time: 20:04
 */

namespace mdocker\lib\curl\multi;


trait loop
{
    public $handle;
    public $active= null;
    public $mrc;
    public $curl=array();
    public $urls;
    function exec_handle(){
        do $this->mrc = curl_multi_exec($this->handle, $this->active);
        while ($this->mrc == ObjectData::perform);
    }
    function create_ch_add_handle(){
        foreach($this->urls as $k=>$v) curl_multi_add_handle($this->handle,$this->curl[$k] = $this->create_ch($this->urls[$k]));
    }
    function getHandle(){
        $this->handle = curl_multi_init();
    }
    function curl_selecct(){
        while ($this->active && $this->mrc == ObjectData::mok) {
            curl_multi_select( $this->handle) != -1 and  usleep(100);
            do $this->mrc = curl_multi_exec( $this->handle, $this->active);
            while ($this->mrc == ObjectData::perform);
        }
    }
    function getResponse(){
        foreach ($this->curl as $k => $v) {
            curl_error($this->curl[$k]) == "" and  $this->result[$k] = (string) curl_multi_getcontent($this->curl[$k]);
            curl_multi_remove_handle($this->handle, $this->curl[$k]);
            curl_close($this->curl[$k]);
        }
        curl_multi_close($this->handle);
        return $this->result;
    }
}