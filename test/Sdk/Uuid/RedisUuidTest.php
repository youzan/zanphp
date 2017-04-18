<?php

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Sdk\Uuid\RedisUuid;
use Zan\Framework\Testing\TaskTest;

class RedisUuidTest extends TaskTest {
    public function initTask()
    {
        //connection pool init
        ConnectionInitiator::getInstance()->init('connection', null);
        parent::initTask();
    }

    public function taskGet()
    {
        $tableName = 'unit_test_uuid_table';
        $res = (yield RedisUuid::getInstance()->get($tableName));
        var_dump($res);
        $serialId = (yield RedisUuid::getInstance()->getSerialId());
        var_dump($serialId);
        $snowflake = (yield RedisUuid::getInstance()->getSnowflake());
        var_dump($snowflake);
        $objId = (yield RedisUuid::getInstance()->getObjectId());
        var_dump($objId);
        yield $res;
    }
}