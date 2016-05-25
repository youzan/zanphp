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
namespace Zan\Framework\Network\Http;

class Response extends \Zan\Framework\Network\Contract\Response {

    public $charset;
    public $content;
    public $data;
    public $format = 'html';
    private $statusCode = 200;
    private $response;
    private $httpStatuses;

    public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
        $this->httpStatuses = StatusCode::$httpStatuses;
    }

    public function send()
    {
        $this->response->end($this->getData());
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($value, $text = null)
    {

    }

    public function setHeader($key, $value)
    {

    }

    public function getHeader()
    {

    }

    public function addHeaders($header)
    {

    }

    public function setCookie($key, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httpOnly = null)
    {

    }

    public function setNoCache()
    {

    }

}