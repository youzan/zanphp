<?php

namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Contract\Future;

class Response implements Future {

    private $code = 0;
    private $message = '';
    private $data = [];

    public function __construct($code=0, $msg='', $data=[]) {
        $this->code = $code;
        $this->message = $msg;
        $this->data = $data;
    }

    public function setCode($code){
        if(!$code) {
            return false;
        }
        $this->code = $code;
    }

    public function getCode(){
        return $this->code;
    }

    public function setMessage($msg){
        if(!$msg) {
            return false;
        }
        $this->message = $msg;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getData(){
        return $this->data;
    }

    public function setData($data){
        if(!$data) {
            return false;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
        if (is_object($data)) {
            $data = serialize($data);
        }
        $this->data = $data;
    }

    public function send()
    {
        return true;
    }

}
