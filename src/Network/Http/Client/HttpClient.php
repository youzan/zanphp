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
        $this->client = new \swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);

        $this->parser = new parser();

        $this->bindEvent();

        swoole_async_dns_lookup($this->host, function($host, $ip) {
            $this->client->connect($ip, $this->port, $this->timeout);
        });
    }


    private function buildHeader()
    {
        $header  = $this->method.' '. $this->uri .' HTTP/1.1'. self::EOF;
        $header .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' . self::EOF;
        $header .= 'Accept-Encoding: gzip,deflate' . self::EOF;
        $header .= 'Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4,ja;q=0.2' . self::EOF;
        $header .= 'Host: '. $this->host . self::EOF;
        $header .= 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36' . self::EOF;
        $header .= 'Connection: close' . self::EOF;

        if ($this->body) {
            if (isset($this->header['content_type'])) {
                $header .= 'Content-Type: ' . $this->header['content_type'] . self::EOF;
            } else {
                $header .= 'Content-Type: application/json' . self::EOF;
            }
            $header .= 'Content-Length: ' . strlen($this->body) . self::EOF;
        }
        return $header;
    }

    private function bindEvent()
    {
        $this->client->on('connect', [$this, 'onConnect']);
        $this->client->on('receive', [$this, 'onReceive']);
        $this->client->on('error',   [$this, 'onError']);
        $this->client->on('close',   [$this, 'onClose']);
    }

    public function onConnect()
    {
        $this->client->send($this->buildHeader() . self::EOF . $this->body);
    }

    public function onReceive($cli, $data)
    {
        if ($this->parser->parse($data) === Parser::FINISHED) {

            call_user_func($this->callback, $this->parser->getBody());
        }
    }

    public function OnError()
    {
        call_user_func($this->callback, "Connect to server failed.");
    }

    public function onClose()
    {
//        $this->client->close();
    }

}