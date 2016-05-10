<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/5/10
 * Time: 10:30
 */

namespace Zan\Framework\Store\NoSQL\Redis;

use Zan\Framework\Utilities\DesignPattern\Singleton;


class RedisInitiator {

    use Singleton;

    public function init($config)
    {
        if (!empty($config)) {
            $redisManager = RedisManager::getInstance();
            $redisClient = new RedisClient($config['server_ip'], $config['port']);
            $redisManager->setRedis($redisClient);
        }

    }

}