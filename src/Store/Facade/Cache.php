<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store\Facade;

use RuntimeException;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    private static $redis=null;

    public static function get($configKey, $key)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = Config::getCache($configKey);
        $realKey = self::getRealKey($cacheKey, $key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->get($realKey));
            yield $result;
        }
    }

    public static function  expire($configKey, $key, $expire=0)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = Config::getCache($configKey);
        $realKey = self::getRealKey($cacheKey, $key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->expire($realKey, $expire));
            yield $result;
        }
    }

    public static function set($configKey, $value, $key)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = Config::getCache($configKey);
        $realKey = self::getRealKey($cacheKey, $key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->set($realKey, $value, $cacheKey['exp']));
            yield $result;
        }
    }

    private static function getRedisConnByConfigKey($configKey)
    {
        $pos= strrpos($configKey, '.');
        $subPath = substr($configKey,0, $pos);
        $config = Config::getCache($subPath);
        if(!isset($config['common'])) {
            throw new RuntimeException('connection path config not found');
        }
        return $config['common']['connection'];
    }

    public static function getRedisManager($configKey)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::getRedisConnByConfigKey($configKey)));
        self::$redis = new RedisManager($conn);
    }

    private static function getRealKey($config, $keys){
        $format = $config['key'];
        if($keys === null){
            return $format;
        }
        if(!is_array($keys)){
            $keys = [$keys];
        }
        $key = call_user_func_array('sprintf', array_merge([$format], $keys));
        return $key;
    }
}