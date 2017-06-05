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
            yield ConnectionManager::getInstance()->get('mysql.default_write');
            yield ConnectionManager::getInstance()->get('redis.default_write');
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}