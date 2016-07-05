<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/12
 * Time: 22:23
 */
namespace Zan\Framework\Store\Facade;

use RuntimeException;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception;
use Zan\Framework\Store\NoSQL\Redis\Redis;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    const POOL_PREFIX = 'connection.';

    private static $_instance = null;
    private static $_configMap = null;
    
    private static function init($connection)
    {
        if (null === self::$_instance[$connection]) {
            self::$_instance[$connection] = new self;
        }
        return self::$_instance[$connection];
    }

    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }


    public static function __callStatic($func, $args) {
        $configKey = array_shift($args);
        $keys = array_shift($args);

        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));

        $redis = new Redis($conn);
        $realKey = self::getRealKey($config, $keys);
        array_unshift($args, $realKey);

        yield call_user_func_array([$redis, $func], $args);
    }

    public static function get($configKey, $keys)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));

        $redis = new Redis($conn);
        $realKey = self::getRealKey($config, $keys);
        $result = (yield $redis->get($realKey));
        $result = self::decode($result);
        yield $result;
    }

    public static function hGet($configKey, $keys, $field = '')
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));

        $redis = new Redis($conn);
        $realKey = self::getRealKey($config, $keys);
        $result = (yield $redis->hGet($realKey, $field));
        $result = self::decode($result);
        yield $result;
    }

    public static function set($configKey, $keys, $value)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));

        $redis = new Redis($conn);
        $realKey = self::getRealKey($config, $keys);
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $result = (yield $redis->set($realKey, $value));
        if ($result) {
            $ttl = isset($config['exp']) ? $config['exp'] : 0;
            yield $redis->expire($realKey, $ttl);
        }
        yield $result;
    }

    /**
     * @param $connection
     * @return \Generator
     * @throws Exception
     * @throws \Zan\Framework\Foundation\Exception\System\InvalidArgumentException
     */
    public function getConnection($connection)
    {
        $conn = (yield ConnectionManager::getInstance()->get($connection));
        if (!$conn instanceof Connection) {
            throw new Exception('Redis get connection error');
        }

        yield $conn;
    }

    /**
     * @param $config
     * @return bool
     */
    private static function validConfig($config)
    {
        if (!$config) {
            return false;
        }

        if (!isset($config['connection'])
            || !isset($config['key'])) {
            return false;
        }

        return true;
    }

    private static function getRealKey($config, $keys){
        $format = isset($config['key']) ? $config['key'] : null ;
        if($keys === null){
            if ($format === null) {
                throw new InvalidArgumentException('expect keys is string or array, null given');
            }
            return $format;
        }
        if(!is_array($keys)){
            $keys = [$keys];
        }

        array_unshift($keys, $format);
        $key = call_user_func_array('sprintf', $keys);
        return $key;
    }

    private static function getConfigCacheKey($configKey)
    {
        $result = self::$_configMap;
        $routes = explode('.', $configKey);
        if (empty($routes)) {
            return null;
        }
        foreach ($routes as $route) {
            if (!isset($result[$route])) {
                return null;
            }
            $result = &$result[$route];
        }
        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function decode($value)
    {
        if(strpos($value,'a:') === 0){
            $value = unserialize($value);
        }elseif(preg_match('/^\s*[\[|\{].*[\]|\}\s*$]/',$value)){
            $value = json_decode($value,true);
        }

        return $value;
    }
}