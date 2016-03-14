<?php

namespace Zan\Framework\Network\Http\Response;

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