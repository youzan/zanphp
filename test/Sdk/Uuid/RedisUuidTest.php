<?php

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Sdk\Uuid\RedisUuid;

use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Network\Connection\Factory\Redis;
use Zan\Framework\Network\Connection\Pool;

require __DIR__ . '/../../bootstrap.php';
class RedisUuidTest extends \TestCase
{
    /**
     * @test
     */
    public function testGet()
    {
        $coroutine = $this->get();

        $task = new Task($coroutine, null, 19);
        $task->run();
    }

    private function get()
    {
//        $this->setConnect();

        $tableName = 'unit_test_uuid_table';
        $res = (yield RedisUuid::getInstance()->get($tableName));
        var_dump($res);
    }

    private function setConnect()
    {
        /*
         * 模拟服务启动时初始化redis连接池的配置
         */
        $factoryType = 'Redis';
        /*
         * 模拟resource/config/redis.php中的配置
         */
        $config = [
            'engine'=> 'redis',
            'host' => '192.168.66.202',
            'port' => 6000,
            'pool'  => [
                'maximum-connection-count' => '50',
                'minimum-connection-count' => '10',
                'keeping-sleep-time' => '10',
                'init-connection'=> '1',
            ],
        ];
        $config['pool']['pool_name'] = 'redis.uuid';
        $factory = new Redis($config);
        $connectionPool = new Pool($factory, $config, $factoryType);
        ConnectionManager::getInstance()->addPool($config['pool']['pool_name'], $connectionPool);
    }
}