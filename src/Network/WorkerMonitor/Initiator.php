<?php

/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/4/26
 * Time: 下午4:57
 */

namespace Zan\Framework\Network\WorkerMonitor;

use Zan\Framework\Utilities\DesignPattern\Singleton;


use Zan\Framework\Network\Server\Timer\Timer;


class Initiator
{
    use Singleton;

    public $classHash;

    public function init($config){
        echo "init\n";
        $this->heartbeat();
    }

    public function heartbeat()
    {
        //绑定心跳检测事件
        $this->classHash = spl_object_hash($this);
        $this->heartbeatLater();
    }


    public function heartbeatLater()
    {
        Timer::tick(5000, $this->classHash,[$this,'heartbeating']);
    }

    public function heartbeating()
    {
        echo "heartbeating\n";
    }

}