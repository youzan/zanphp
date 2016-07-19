<?php
namespace Zan\Framework\Network\Common;

class Response
{
    private $body;
    private $headers;
    private $statusCode;

    public function __construct($statusCode, $headers = null, $body = null)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($header)
    {
        return $this->headers[$header];
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}