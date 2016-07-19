<?php
namespace Zan\Framework\Network\Common;

class Response
{
    private $response;
    private $header;
    private $code;

    public function __construct($response, $header, $code)
    {
        $this->response = $response;
        $this->header = $header;
        $this->code = $code;
    }

    public function getResponseJson()
    {
        $response = $this->response;
        $jsonData = json_decode($response, true);
        $response = $jsonData ? $jsonData : $response;

        return $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getResponseHeader()
    {
        return $this->header;
    }

    public function getResponseCode()
    {
        return $this->code;
    }
}