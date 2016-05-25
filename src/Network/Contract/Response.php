<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
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
