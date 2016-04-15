<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Network\Http\Client\HttpClient as HClient;
use Zan\Framework\Foundation\Contract\Async;

class HttpClient implements Async
{
    const GET = 'GET';
    const POST = 'POST';

    /** @var  HClient */
    private $client;

    private $host;
    private $port;

    private $timeout;

    private $uri;
    private $method;

    private $params;

    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function newInstance($host, $port)
    {
        return new static($host, $port);
    }

    public function get($uri = '', $params = [], $timeout = 3)
    {
        $this->setMethod(self::GET);
        $this->setTimeout($timeout);
        $this->setUri($uri);
        $this->setParams($params);

        yield $this->build();
    }

    public function post($uri = '', $params = [], $timeout = 3)
    {
        $this->setMethod(self::POST);
        $this->setTimeout($timeout);
        $this->setUri($uri);
        $this->setParams($params);

        yield $this->build();
    }

    public function execute(callable $callback)
    {
        $this->client->setCallback($this->getCallback($callback))->handle();
    }

    private function setMethod($method)
    {
        $this->method = $method;
    }

    private function setUri($uri)
    {
        if (empty($uri)) {
            $uri .= '/';
        }
        $this->uri = $uri;
    }

    private function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function setParams($params)
    {
        $this->params = $params;
    }

    private function build()
    {
        $this->client = new HClient($this->host, $this->port);

        $this->client->setTimeout($this->timeout);
        $this->client->setMethod($this->method);

        if ($this->method != 'POST' and $this->method != 'PUT') {
            if (!empty($this->params)) {
                $this->uri = $this->uri . '?' . http_build_query($this->params);
            }
        } else {
            $body = json_encode($this->params);
            $contentType = 'application/json';
            $this->client->setHeader([
                'content_type' => $contentType
            ]);
            $this->client->setBody($body);
        }
        $this->client->setUri($this->uri);

        return $this;
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            $jsonData = json_decode($response, true);
            $response = $jsonData ? $jsonData : $response;
            call_user_func($callback, $response);
        };
    }
}