<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:13
 */

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
        } catch (\Exception $e) {
            //todo log
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
        echo "redis client close\n";
    }

    public function onConnect($redis, $res) {
        if (false === $res) {
            //TODO: connect失败
        }
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        //put conn to active_pool
        $this->release();
        ReconnectionPloy::getInstance()->connectSuccess(spl_object_hash($this));
        echo "redis client connect to server\n";
    }

    public function onMessage($redis, $message) {

    }

}