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
namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;

class HttpClient implements Async
{
    const GET = 'GET';
    const POST = 'POST';

    /** @var  swoole_http_client */
    private $client;

    private $host;
    private $port;
    private $ssl;

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
        $this->client = new \swoole_http_client($ip, $this->port, $this->ssl);
        $this->buildHeader();

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
}
