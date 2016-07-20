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
        if (is_array($headers)) {
            $this->headers = $headers;
        } else {
            $this->headers = [];
        }
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
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return null;
        }
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}