<?php

namespace Zan\Framework\Network\Connection\Driver;

use Zan\Framework\Contract\Network\Connection;
use swoole_client as SwooleClient;
use Zan\Framework\Network\Connection\ReconnectionPloy;
use Zan\Framework\Network\Server\Timer\Timer;


class Tcp extends Base implements Connection
{
    private $clientCb;
    protected $isAsync = true;

    public function closeSocket()
    {
        try {
            $this->getSocket()->close();
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }

    public function init() {
        //set callback
        $this->getSocket()->on('connect', [$this, 'onConnect']);
        $this->getSocket()->on('receive', [$this, 'onReceive']);
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('error', [$this, 'onError']);
    }

    public function onConnect($cli) {
        //put conn to active_pool
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->release();

        ReconnectionPloy::getInstance()->connectSuccess(spl_object_hash($this));
        sys_echo("tcp client connect to server " . $this->getConnString());
    }

    public function onClose(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->close();
        sys_echo("tcp client close " . $this->getConnString());
    }

    public function onReceive(SwooleClient $cli, $data) {
        call_user_func($this->clientCb, $data);
    }

    public function onError(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->close();
        sys_error("tcp client error " . $this->getConnString());
    }

    public function setClientCb(callable $cb) {
        $this->clientCb = $cb;
    }
}