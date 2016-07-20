<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Sdk\Trace\Constant;

class HttpClient implements Async
{
    const GET = 'GET';
    const POST = 'POST';

    /** @var  swoole_http_client */
    private $client;

    private $host;
    private $port;
    private $https;

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
    private $trace;

    private $gateway = false;

    public function __construct($host, $port = 80, $https = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->https = $https;
    }

    public static function newInstance($host, $port = 80, $https = false)
    {
        return new static($host, $port, $https);
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

    public function execute(callable $callback, $task)
    {
        $this->setCallback($this->getCallback($callback))->handle();
    }

    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        return $this;
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
                throw new HttpClientTimeoutException('Timeout must be between 0-60 seconds');
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
        $this->trace = (yield getContext('trace'));

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

        yield $this;
    }

    public function setCallback(Callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function handle()
    {
        $runMode = RunMode::get();
        if (($runMode === 'online' || $runMode === 'pre') && $this->gateway) {
            $this->request('10.200.175.195');
        } else {
            swoole_async_dns_lookup($this->host, function($host, $ip) {
                $this->request($ip);
            });
        }
    }


    public function request($ip)
    {
        $this->client = new \swoole_http_client($ip, $this->port);
        $this->buildHeader();
        if (null !== $this->timeout) {
            Timer::after($this->timeout, [$this, 'checkTimeout'], spl_object_hash($this));
        }

        if ($this->trace) {
            $this->trace->transactionBegin(Constant::HTTP_CALL, $this->host . $this->uri);
        }

        if('GET' === $this->method){
            if ($this->trace) {
                $this->trace->logEvent(Constant::GET, Constant::SUCCESS);
            }
            $this->client->get($this->uri, [$this,'onReceive']);
        }elseif('POST' === $this->method){
            if ($this->trace) {
                $this->trace->logEvent(Constant::POST, Constant::SUCCESS, $this->body);
            }
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
        if ($this->https) {
            $this->header['scheme'] = 'https';
        }

        $this->client->setHeaders($this->header);
    }

    public function onReceive($cli)
    {
        Timer::clearAfterJob(spl_object_hash($this));
        if ($this->trace) {
            $this->trace->commit(Constant::SUCCESS);
        }
        $response = new Response($cli->statusCode, $cli->headers, $cli->body);
        call_user_func($this->callback, $response);
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            call_user_func($callback, $response);
        };
    }

    public function checkTimeout()
    {
        $this->client->close();
        $exception = new HttpClientTimeoutException();
        if ($this->trace) {
            $this->trace->commit($exception);
        }
        call_user_func_array($this->callback, [null, $exception]);
    }
}