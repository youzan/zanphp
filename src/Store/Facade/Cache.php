<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store;

use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    public function get($key)
    {
        $result = (yield RedisManager::getInstance()->get($key));
        yield $result;
    }

    public function set($key, $value)
    {
        $result = (yield RedisManager::getInstance()->set($key, $value));
        yield $result;
    }
}