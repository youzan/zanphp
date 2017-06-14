<?php

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\ReconnectionPloy;
use Zan\Framework\Network\Server\Timer\Timer;

class Redis extends Base implements Connection
{
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
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('message', [$this, 'onMessage']);
    }

    public function onClose($redis){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->close();
        sys_echo("redis client close " . $this->getConnString());
    }

    public function onConnect($redis, $res) {
        // 避免swoolebug
        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->getSocket()->isClosed) {
            if ($res) {
                $this->getSocket()->close();
            }
            return;
        }


        Timer::clearAfterJob($this->getConnectTimeoutJobId());

        if (false === $res) {
            sys_error("redis client connect error" . $this->getConnString());
            $this->close();
            return;
        }
        //put conn to active_pool
        $this->release();
        ReconnectionPloy::getInstance()->connectSuccess(spl_object_hash($this));
        sys_echo("redis client connect to server " . $this->getConnString());
    }

    public function onMessage($redis, $message) {

    }

}