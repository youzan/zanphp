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

class MonitorConnectionNum {

    use Singleton;

    private static $poolMap=[];

    public function controlLinkNum($poolMap)
    {
        self::$poolMap = $poolMap;
        $config = Config::get('connection.reconnection');
        $time = isset($config['interval-reduce-link'])?  $config['interval-reduce-link'] : 60000;
        Timer::tick($time, [$this, 'reduceLinkNum']);
    }

    public function reduceLinkNum()
    {
        $config = Config::get('connection.reconnection');
        $reduceNum = isset($config['num-reduce-link']) ? $config['num-reduce-link'] : 1 ;
        foreach (self::$poolMap as $poolKey => $pool) {
            $activeNums = $pool->getActiveConnection()->length();
            $freeNums = $pool->getFreeConnection()->length();
            $sumNums = $activeNums + $freeNums;
            if ($sumNums <=0 || $freeNums*3 < $sumNums) {
                continue;
            }
            for ($i=0; $i<$reduceNum; $i++) {
                $conn = $pool->getFreeConnection()->pop();
                $conn->closeSocket();
            }
        }
    }

}