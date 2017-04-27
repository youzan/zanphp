<?php
namespace Zan\Framework\Test\Sdk\Uuid;

use Zan\Framework\Sdk\Uuid\RedisUuid;
use Zan\Framework\Store\NoSQL\Exception\RedisCallTimeoutException;
use Zan\Framework\Testing\TaskTest;

class RedisUuidTest extends TaskTest {
    public function taskGet()
    {
        try {
            $tableName = 'unit_test_uuid_table';
            $res = (yield RedisUuid::getInstance()->get($tableName));
            $this->assertTrue(is_integer($res));
            $serialId = (yield RedisUuid::getInstance()->getSerialId());
            $this->assertTrue(is_integer($serialId));
            $snowflake = (yield RedisUuid::getInstance()->getSnowflake());
            $this->assertTrue(is_integer($snowflake));
            $objId = (yield RedisUuid::getInstance()->getObjectId());
            $this->assertTrue(is_string($objId));
            yield $res;
        } catch (\Exception $e) {
            $this->assertInstanceOf(RedisCallTimeoutException::class, $e, $e->getMessage());
            return;
        }

    }
}