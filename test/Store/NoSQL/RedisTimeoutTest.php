<?php
namespace Zan\Framework\Test\Store\NoSQL;

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception\RedisCallTimeoutException;
use Zan\Framework\Store\NoSQL\Redis\Redis;
use Zan\Framework\Network\Connection\Factory\Redis as RedisFactory;
use Zan\Framework\Testing\TaskTest;

/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/30
 * Time: 上午10:06
 */
class RedisTimeoutTest extends TaskTest {
    public function taskConnectFailed()
    {
        $config  = [
            'host' => '127.0.0.1',
            'port' => 1234,
            'connect_timeout' => 1,
        ];
        $factory = new RedisFactory($config);
        $conn = $factory->create();
        $this->assertNull($conn, "Expected create return null");
    }

    public function taskCallTimeout()
    {
        try {
            //redis connection pool init
            ConnectionInitiator::getInstance()->init('connection.redis', null);
            $conn = (yield ConnectionManager::getInstance()->get("default_write"));
            $config = $this->getProperty($conn, "config");
            $config["timeout"] = 1;
            $this->setPropertyValue($conn, "config", $config);
            $redis = new Redis($conn);
            yield $redis->set("foo", "value");
        } catch (RedisCallTimeoutException $e) {
            $this->assertEquals($e->getMessage(), "Redis call set timeout");
            return;
        }
        $this->fail("Expected RedisCallTimeoutException has not been raised.");
    }
}