<?php


namespace mdocker\lib\curl;

/*1步：curl_multi_init初始化curl批处理句柄资源$handle

2步：curl_multi_add_handle向$handle加$ch                                                                        //   循环
3步：curl_multi_exec解析$handle                                                                                      //     循环

4步：curl_multi_getcontent取输出文本流                                                                                // 循环

5步：curl_multi_remove_handle移除$handle中$ch，为每handle调curl_close                                                关闭

*/

class mcurl
{


    public $data;


    function __get($name){
       $value=isset($this->data->$name)?$this->data->$name:null;
       return $value;
    }

    function __set($name, $value){
        $this->data->$name=$value;
        return true;
    }
    function __call($name, $arguments){
          $obj=  new \ReflectionObject($this->data);
         if (!$obj->hasMethod($name))  return null;
         $methods=$obj->getMethod($name) ;
        return   $methods->invokeArgs($this->data,$arguments);
    }
}


