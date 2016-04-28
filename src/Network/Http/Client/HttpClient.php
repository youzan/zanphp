<?php

namespace Zan\Framework\Network\Http\Client;

class HttpClient
{
    const EOF = "\r\n";
    const DEFAULT_PORT = 80;
    const DEFAULT_TIMEOUT = 3;

    /** @var \swoole_client  */
    private $client;

    /** @var  Callable */
    private $callback;

    private $host;
    private $port;

    private $timeout;

    private $uri;
    private $method;

    private $header = [];
    private $body;

    /** @var  Parser */
    private $parser;

    public function __construct($host, $port)
    {
        $this->host    = $host;
        $this->port    = $port ? $port : self::DEFAULT_PORT;
        $this->timeout = self::DEFAULT_TIMEOUT;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setHeader(array $header)
    {
        $this->header = array_merge($this->header, $header);
    }

    public function setBody($body)
    {
        $this->body = $body;
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

}