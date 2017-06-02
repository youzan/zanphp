<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Common\Exception\DnsLookupTimeoutException;
use Zan\Framework\Network\Common\Exception\HostNotFoundException;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;

class HttpClient implements Async
{
    const GET = 'GET';
    const POST = 'POST';

    /** @var  \swoole_http_client */
    private $client;

    private $host;
    private $port;
    private $ssl;

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

    public function __construct($host, $port = 80, $ssl = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
    }

    public static function newInstance($host, $port = 80, $ssl = false)
    {
        return new static($host, $port, $ssl);
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

    public function postJson($uri = '', $params = [], $timeout = 3000)
    {
        $this->setMethod(self::POST);
        $this->setTimeout($timeout);
        $this->setUri($uri);
        $this->setParams(json_encode($params));

        $this->setHeader([
            'Content-Type' => 'application/json'
        ]);

        yield $this->build();
    }

    public function execute(callable $callback, $task)
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
        if (null !== $timeout) {
            if ($timeout < 0 || $timeout > 60000) {
                throw new InvalidArgumentException("Timeout must be between 0-60 seconds, $timeout is given");
            }
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
        if ($this->method === 'GET') {
            if (!empty($this->params)) {
                $this->uri = $this->uri . '?' . http_build_query($this->params);
            }
        } else if ($this->method === 'POST') {
            $body = $this->params;

            $this->setBody($body);
        }

        yield $this;
    }

    public function setCallback(Callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function handle()
    {
        $host = $this->host;
        $port = $this->port;

        $dnsCallbackFn = function($host, $ip) use ($port) {
            if ($ip) {
                $this->request($ip, $port);
            } else {
                $this->whenHostNotFound($host);
            }
        };

        if ($this->timeout === null) {
            DnsClient::lookupWithoutTimeout($host, $dnsCallbackFn);
        } else {
            DnsClient::lookup($host, $dnsCallbackFn, [$this, "dnsLookupTimeout"], $this->timeout);
        }
    }

    public function request($ip, $port)
    {
        $this->client = new \swoole_http_client($ip, $port, $this->ssl);
        $this->buildHeader();
        if (null !== $this->timeout) {
            Timer::after($this->timeout, [$this, 'checkTimeout'], spl_object_hash($this));
        }

        if('GET' === $this->method){
            $this->client->get($this->uri, [$this,'onReceive']);
        }elseif('POST' === $this->method){
            $this->client->post($this->uri,$this->body, [$this, 'onReceive']);
        }
    }

    private function buildHeader()
    {
        if ($this->port !== 80) {
            $this->header['Host'] = $this->host . ':' . $this->port;
        } else {
            $this->header['Host'] = $this->host;
        }

        if ($this->ssl) {
            $this->header['Scheme'] = 'https';
        }

        $this->client->setHeaders($this->header);
    }

    public function onReceive($cli)
    {
        Timer::clearAfterJob(spl_object_hash($this));
        $response = new Response($cli->statusCode, $cli->headers, $cli->body);
        call_user_func($this->callback, $response);
        $this->client->close();
    }

    public function whenHostNotFound($host)
    {
        $ex = new HostNotFoundException("", 408, null, [ "host" => $host ]);
        call_user_func($this->callback, null, $ex);
    }

    private function getCallback(callable $callback)
    {
        return function($response, $exception = null) use ($callback) {
            call_user_func($callback, $response, $exception);
        };
    }

    public function checkTimeout()
    {
        $this->client->close();

        $message = sprintf(
            '[http request timeout] host:%s port:%s uri:%s method:%s ',
            $this->host,
            $this->port,
            $this->uri,
            $this->method
        );
        $metaData = [
            'host' => $this->host,
            'port' => $this->port,
            'ssl' => $this->ssl,
            'uri' => $this->uri,
            'method' => $this->method,
            'params' => $this->params,
            'body' => $this->body,
            'header' => $this->header,
            'timeout' => $this->timeout,
        ];

        $exception = new HttpClientTimeoutException($message, 408, null, $metaData);

        call_user_func($this->callback, null, $exception);
    }

    public function dnsLookupTimeout()
    {
        $message = sprintf(
            '[http dns lookup timeout] host:%s port:%s uri:%s method:%s ',
            $this->host,
            $this->port,
            $this->uri,
            $this->method
        );
        $metaData = [
            'host' => $this->host,
            'port' => $this->port,
            'ssl' => $this->ssl,
            'uri' => $this->uri,
            'method' => $this->method,
            'params' => $this->params,
            'body' => $this->body,
            'header' => $this->header,
            'timeout' => $this->timeout,
        ];

        $exception = new DnsLookupTimeoutException($message, 408, null, $metaData);

        call_user_func($this->callback, null, $exception);
    }
}