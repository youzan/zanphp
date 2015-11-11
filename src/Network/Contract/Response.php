<?php

namespace Zan\Framework\Network\Contract;

class Response {
    private $code = 0;
    private $message = '';
    private $data = '';
    private $exception = null;

    public function __construct($code=null, $msg=null, $data=null, \Exception $exception=null) {
        if(null !== $code) {
            $this->code = $code;
        }

        if(null !== $msg) {
            $this->message = $msg;
        }

        if(null !== $data) {
            $this->data = $data;
        }

        if(null !== $exception) {
            $this->exception = $exception;
        }
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
        $this->data = $data;
    }

    public function getException(){
        return $this->exception;
    }

    public function setException(\Exception $e){
        $this->exception = $e;
    }

}
