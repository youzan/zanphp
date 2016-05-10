<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    public function get($key)
    {
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield RedisManager::getInstance()->get($realKey['key']));
            yield $result;
        }
    }

    public function set($key, $value)
    {
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield RedisManager::getInstance()->set($realKey['key'], $value));
            yield $result;
        }
    }
}