<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/4/12
 * Time: 10:15
 */


namespace Zan\Framework\Test\Network\Connection;

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\Redis;
use Zan\Framework\Testing\TaskTest;

class ConnectionInitiatorTest extends TaskTest {

    protected function initTask()
    {
        $config  = [
            'redis' => [
                'engine'=> 'redis',
                'host' => '127.0.0.1',
                'port' => 6379,
                'pool'  => [
                    'maximum-connection-count' => 50,
                    'minimum-connection-count' => 10,
                    'keeping-sleep-time' => 10,
                    'init-connection'=> 10,
                ],
            ],
        ];
        $connectionInitiator = ConnectionInitiator::getInstance();
        $this->invoke($connectionInitiator, "initConfig", [$config]);
        parent::initTask();
    }

    public function taskPoolGet()
    {
        $conn = (yield ConnectionManager::getInstance()->get("redis"));
        $redis = new Redis($conn);
        yield $redis->set("foo", "value");
        $result = (yield $redis->get("foo"));
        $this->assertEquals($result, "value", "redis get failed");
    }

}