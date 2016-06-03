<?php

namespace Zan\Framework\Sdk\Log\Track;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use \swoole_client;

class TrackClient implements Async{
    private $host;
    private $port = 5140;
    private $timeout = 1;
    private $callback;
    private $postData;
    private $clientConfKey = 'log.client';

    /**
     * @var swoole_client
     */
    private $client = null;

    public function __construct() {
        $this->client = new swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);
        $config = Config::get($this->clientConfKey);

        //test config
        $config = [
            'host' => '192.168.66.204',
            'port' => 5140,
            'timeout' => 1,
        ];
        $this->host    = $config['host'];
        $this->port    = $config['port'] ? $config['port'] : $this->port;
        $this->timeout = $config['timeout'] ? $config['time'] : $this->timeout;
    }

    public function send($log)
    {
        $this->postData = $log . "\n";
        $this->bindEvent();
        $this->client->connect($this->host, $this->port);
        return $this;
    }

    public function execute(callable $callback, $task){
        $this->callback = $callback;
    }

    private function bindEvent()
    {
        $this->client->on('connect', [$this, 'onConnect']);
        $this->client->on('receive', [$this, 'onReceive']);
        $this->client->on('error',   [$this, 'onError']);
        $this->client->on('close',   [$this, 'onClose']);
    }

    public function onReceive($cli, $data)
    {
        call_user_func($this->callback, true);
    }

    public function OnError()
    {
        call_user_func($this->callback, "Connect to server failed.");
    }

    public function onClose($cli)
    {
        //$this->client->close();
    }

    public function onConnect() {
        $this->client->send($this->postData);
        $this->client->close();
    }
}