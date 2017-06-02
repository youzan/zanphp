<?php
namespace Zan\Framework\Test\Store\NoSQL;

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception\RedisCallTimeoutException;
use Zan\Framework\Store\NoSQL\Redis\Redis;
use Zan\Framework\Testing\TaskTest;

/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/30
 * Time: 上午10:06
 */
class RedisTimeoutTest extends TaskTest {
    public function taskCallTimeout()
    {
        try {
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