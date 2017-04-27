<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:20
 */

namespace Zan\Framework\Test\Network\Connection;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Testing\TaskTest;

class ConnectionPoolTest extends TaskTest {
    public function taskPoolWork()
    {
        try {
            $conn = (yield ConnectionManager::getInstance()->get('mysql.default_write'));
            //$pool->close();
            $conn = (yield ConnectionManager::getInstance()->get('redis.default_write'));
//        $pool->close();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}