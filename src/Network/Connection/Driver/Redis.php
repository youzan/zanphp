<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:13
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;

class Redis extends Base implements Connection
{
    protected $isAsync = true;

    protected function closeSocket()
    {
        return true;
    }

    public function init() {
        //set callback
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('message', [$this, 'onMessage']);
    }

    public function onClose($redis){
        $this->close();
        echo "redis client close\n";
    }

    public function onConnect($redis, $res) {
        if (false === $res) {
            //TODO: connect失败
        }
        //put conn to active_pool
        $this->release();
        echo "redis client connect to server\n";
    }

    public function onMessage($redis, $message) {

    }

}