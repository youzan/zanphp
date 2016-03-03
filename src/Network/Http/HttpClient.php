<?php

namespace Zan\Framework\Network\Http;

use \swoole_client;
use Zan\Framework\Foundation\Core\Config;

class HttpClient {

    /**
     * @var swoole_client
     */
    private $client;

    const EOF = '\r\n';

    protected $host;
    protected $port = 80;
    protected $path;
    protected $method;

    protected $request;
    protected $timeout;

    protected $post_data = '';
    protected $callback;
    protected $user_agent = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';

    private $clientConfKey = 'http.client';

    public function __construct($path, $parameter = [], $method = 'POST')
    {
        $config = Config::get($this->clientConfKey);

        $this->host    = $config['host'];
        $this->port    = $config['port'] ? $config['port'] : $this->port;
        $this->timeout = $config['timeout'];
        $this->method  = $method;

        $this->setPath($path);
        $this->buildParams($parameter);
    }

    public function call(callable $callback = null)
    {
        $this->client = new swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);

        $this->bindEvent();
        $this->client->connect($this->host, $this->port, $this->timeout);
    }

    private function setPath($path)
    {
        if (false === strpos($path, '.')) {
            return false;
        }
        $this->path = str_replace('//', '.', $path);
    }

    private function buildParams($parameter)
    {
        if (is_string($parameter)) {
            $this->post_data = $parameter;
        }
        else if (is_array($parameter) || is_object($parameter)) {
            $this->post_data = http_build_query($parameter);;
        }
    }

    private function parseHeader($header)
    {

    }

    private function buildHeader()
    {
        $header  = $this->method.' '. $this->path .' HTTP/1.1'. self::EOF;
        $header .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' . self::EOF;
        $header .= 'Accept-Encoding: gzip,deflate' . self::EOF;
        $header .= 'Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4,ja;q=0.2' . self::EOF;
        $header .= 'Host: '. $this->host . self::EOF;
        $header .= $this->user_agent . self::EOF;

        if ($this->post_data) {
            $header .= 'Content-Type: application/x-www-form-urlencoded' . self::EOF;
            $header .= 'Content-Length: ' . strlen($this->post_data) . self::EOF;
        }
        return $header;
    }

    private function bindEvent()
    {
        $this->client->on('connect', [$this, 'onConnect']);
        $this->client->on('receive', [$this, 'onReceive']);
        $this->client->on('error',   [$this, 'onError']);
        $this->client->on('close',   [$this, 'close']);
    }

    private function onConnect(\swoole_client $cli)
    {
        $cli->send($this->buildHeader() . self::EOF . $this->post_data);
    }

    private function onReceive($cli, $data)
    {
        var_dump($data);exit;
    }

    private function OnError()
    {
        echo "Connect to server failed.\n";
    }

    private function onClose()
    {
        $this->client->close();
    }



}