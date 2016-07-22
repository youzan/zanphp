<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/7/21
 * Time: 17:46
 */

namespace Zan\Framework\Network\Connection;



use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\Types\Time;

class MonitorConnectionNum {

    use Singleton;

    private static $poolMap=[];

    public function controlLinkNum($poolMap)
    {
        self::$poolMap = $poolMap;
        $config = Config::get('reconnection');
        $time = isset($config['interval-reduce-link'])?  $config['interval-reduce-link'] : 60000;
        Timer::tick($time, [$this, 'reduceLinkNum']);
    }

    public function reduceLinkNum()
    {
        $config = Config::get('reconnection');
        $timeInterval = isset($config['interval-reduce-link'])?  $config['interval-reduce-link'] : 60000;
        foreach (self::$poolMap as $poolKey => $pool) {
            $activeNum = $pool->getActiveConnection()->length();
            $freeNum = $pool->getFreeConnection()->length();
            $sumNum = $activeNum + $freeNum;
            if ($sumNum <=0 || $freeNum*4 < $sumNum) {
                continue;
            }
            for ($i=0; $i<$freeNum; $i++) {
                $conn = $pool->getFreeConnection()->pop();
                if ($conn->lastUsedTime == 0 || (Time::current(true) - $conn->lastUsedTime) > $timeInterval/1000) {
                    $conn->closeSocket();
                }
            }
        }
    }
}