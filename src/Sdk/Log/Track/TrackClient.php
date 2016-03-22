<?php

namespace Zan\Framework\Sdk\Log\Track;

use Zan\Framework\Foundation\Contract\Async;
use \swoole_client;

class TrackClient implements Async{
    private $host = "192.168.66.204";
    private $port = 5140;
    private $callback;
    private $postData;

    /**
     * @var swoole_client
     */
    private $client = null;

    public function __construct() {


        $this->client = new swoole_client(SWOOLE_TCP, SWOOLE_SOCK_ASYNC);

    }

    public function send($log)
    {
        $this->postData = $log . "\n";
        $this->bindEvent();
        $this->client->connect($this->host, $this->port);
    }

    public function execute(callable $callback){
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