<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;

class HttpClient implements Async
{
    const GET = 'GET';
    const POST = 'POST';

    /** @var  swoole_http_client */
    private $client;

    private $host;
    private $port;

    /**
     * @var int [millisecond]
     */
    private $timeout;

    private $uri;
    private $method;

    private $params;
    private $header = [];
    private $body;

    private $callback;

    public function __construct($host, $port = 80)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function newInstance($host, $port = 80)
    {
        return new static($host, $port);
    }

    public function get($uri = '', $params = [], $timeout = 3000)
    {
        $this->setMethod(self::GET);
        $this->setTimeout($timeout);
        $this->setUri($uri);
        $this->setParams($params);

        yield $this->build();
    }

    public function post($uri = '', $params = [], $timeout = 3000)
    {
        $this->setMethod(self::POST);
        $this->setTimeout($timeout);
        $this->setUri($uri);
        $this->setParams($params);

        yield $this->build();
    }

    public function execute(callable $callback)
    {
        $this->setCallback($this->getCallback($callback))->handle();
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setUri($uri)
    {
        if (empty($uri)) {
            $uri .= '/';
        }
        $this->uri = $uri;
        return $this;
    }

    public function setTimeout($timeout)
    {
        if ($timeout < 0 || $timeout > 60000) {
            throw new HttpClientTimeoutException('Timeout must be between 0-60 seconds');
        }
        $this->timeout = $timeout;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function setHeader(array $header)
    {
        $this->header = array_merge($this->header, $header);
        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    private function build()
    {
        if ($this->method != 'POST' and $this->method != 'PUT') {
            if (!empty($this->params)) {
                $this->uri = $this->uri . '?' . http_build_query($this->params);
            }
        } else {
            $body = json_encode($this->params);
            $contentType = 'application/json';
            $this->setHeader([
                'Content-Type' => $contentType
            ]);
            $this->setBody($body);
        }

        return $this;
    }

    public function setCallback(Callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function handle()
    {
        swoole_async_dns_lookup($this->host, function($host, $ip) {
            $this->request($ip);
        });
    }


    public function request($ip)
    {
        $this->client = new \swoole_http_client($ip, $this->port);
        $this->buildHeader();
        Timer::after($this->timeout, [$this, 'checkTimeout'], spl_object_hash($this));
        if('GET' === $this->method){
            $this->client->get($this->uri, [$this,'onReceive']);
        }elseif('POST' === $this->method){
            $this->client->post($this->uri,$this->body, [$this, 'onReceive']);
        }
    }

    private function buildHeader()
    {
        $this->header['Host'] = $this->host;
        $this->client->setHeaders($this->header);
    }

    public function onReceive($cli)
    {
        Timer::clearAfterJob(spl_object_hash($this));
        call_user_func($this->callback, $cli->body);
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            $jsonData = json_decode($response, true);
            $response = $jsonData ? $jsonData : $response;
            call_user_func($callback, $response);
        };
    }

    public function checkTimeout()
    {
        $this->client->close();
        $exception = new HttpClientTimeoutException();
        call_user_func_array($this->callback, [null, $exception]);
    }
}