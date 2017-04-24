<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network\Connection;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Testing\TaskTest;

class ConnectionPoolTest extends TaskTest {
    public function initTask()
    {
        //connection pool init
        ConnectionInitiator::getInstance()->init('connection', null);
        parent::initTask();
    }

    public function taskPoolWork()
    {
        for ($i=0; $i<5; $i++) {
            $pool = (yield ConnectionManager::getInstance()->get('mysql.default_write'));
            $pool->close();
            $pool = (yield ConnectionManager::getInstance()->get('redis.default_write'));
            $pool->close();
        }
    }
}