<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:36
 */

namespace Zan\Framework\Network\Connection\Driver;

use Zan\Framework\Contract\Network\Connection;
use swoole_client as SwooleClient;

class Syslog extends Base implements Connection
{
    private $clientCb;
    private $postData;

    public function init()
    {
        //set callback
        $this->getSocket()->on('connect', [$this, 'onConnect']);
        $this->getSocket()->on('receive', [$this, 'onReceive']);
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('error', [$this, 'onError']);
    }

    public function send($log)
    {
        $this->postData = $log . "\n";
        $this->setClientCb([$this, 'ioReady']);
        $this->conn->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
    }

    public function onConnect($cli)
    {
        $this->getSocket()->send($this->postData);
    }

    public function onClose(SwooleClient $cli)
    {
        $this->close();
    }

    public function onReceive(SwooleClient $cli, $data)
    {
        call_user_func($this->clientCb, $data);
    }

    public function ioReady()
    {
        $this->release();
    }

    public function onError(SwooleClient $cli)
    {
        $this->close();
        echo "nova client error\n";
    }

    public function setClientCb(callable $cb)
    {
        $this->clientCb = $cb;
    }

    protected function closeSocket()
    {
        return true;
    }
}
