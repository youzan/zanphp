<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store\Facade;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    private static $redis=null;

    public static function get($key)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::connectionPath($key)));
        $socket = $conn->getSocket();
        self::$redis = new RedisManager($socket);
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->get($realKey['key']));
            yield $result;
        }
        $conn->release();
    }

    public static function set($key, $value)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::connectionPath($key)));
        $socket = $conn->getSocket();
        self::$redis = new RedisManager($socket);
        $realKey = Config::get($key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->set($realKey['key'], $value));
            yield $result;
        }
        $conn->release();
    }

    private static function connectionPath($path)
    {
        $pos= strrpos($path, '.');
        $subPath = substr($path,0, $pos);
        $config = Config::get($subPath);
        return $config['connection'];
    }
}