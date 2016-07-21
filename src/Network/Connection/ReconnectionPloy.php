<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/7/21
 * Time: 15:49
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Server\Timer\Timer;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class ReconnectionPloy {

    use Singleton;

    private $config=[];

    private $reconnectTime=[];

    public function init()
    {
        $this->config = Config::get('connection.reconnection');
    }

    public function reconnect($conn, $pool)
    {
        $connHashCode = spl_object_hash($conn);
        $reconnectTime =$this->reconnectTime[$connHashCode];
        $intervalTime = isset($this->config['interval-reconnect-time']) ? $this->config['interval-reconnect-time'] : 5000;
        $maxTime = isset($this->config['max-reconnect-time']) ? $this->config['max-reconnect-time'] : 30000;
        $this->reconnectTime[$connHashCode] = ($reconnectTime+$intervalTime) >= $maxTime ?
            $maxTime :($reconnectTime+$intervalTime);
        Timer::after($reconnectTime, [$pool, 'createConnect']);
    }

    public function cleanReconnectTime($key)
    {
        unset($this->reconnectTime[$key]);
    }

    public function getReconnectTime($key)
    {
        if(isset($this->reconnectTime[$key])){
            yield $this->reconnectTime[$key];
        } else {
            yield false;
        }
        return;
    }

    public function setReconnectTime($key, $value)
    {
        $this->reconnectTime[$key] = $value;
    }


}