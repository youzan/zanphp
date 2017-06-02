<?php

namespace Zan\Framework\Store\Facade;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\Redis;
use Zan\Framework\Utilities\Types\ObjectArray;

/**
 * Class Cache
 * @package Zan\Framework\Store\Facade
 *
 * @method static bool del($configKey, $keys)
 */
class Cache
{

    const POOL_PREFIX = 'connection.';

    const ACTIVE_CONNECTION_CONTEXT_KEY= 'redis_active_connections';

    private static $_instance = null;
    private static $_configMap = null;

    /**
     * @param $connection
     * @return static
     */
    private static function init($connection)
    {
        if (!isset(self::$_instance[$connection]) || null === self::$_instance[$connection]) {
            self::$_instance[$connection] = new self;
        }
        return self::$_instance[$connection];
    }

    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }

    /**
     * 给初始化后的Cache配置追加配置项
     *
     * @param string $configKey
     * @param array $config
     * @return null
     */
    public static function appendConfigMapByConfigKey($configKey, array $config)
    {
        if (self::validConfig($config)) {
            $routes = explode('.', $configKey);
            if (empty($routes)) {
                return null;
            }
            //如果key已有值就放弃设值（避免重复覆盖）
            $nowConfig = self::getConfigCacheKey($configKey);
            if (!empty($nowConfig)) {
                return null;
            }

            $result = [];
            $routes = array_reverse($routes);
            foreach ($routes as $key => $route) {
                $result = ($key == 0) ? [$route => $config] : [$route => $result];
            }
            if (is_array(self::$_configMap) && is_array($result)) {
                self::$_configMap = array_merge_recursive(self::$_configMap, $result);
            }
        }
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
        $result = (yield $redis->$func($realKey, ...$args));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
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
        //gzdecode
        if ($result && isset($config['encode']) && $config['encode'] == 'gz') {
            $result = gzdecode($result);
        }
        $result = self::decode($result);

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    /* hash ops start ****************************************/
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

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hMGet($configKey, $keys, $fields)
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
        $oriFields = $fields;
        $results = (yield $redis->hMGet($realKey, ...$fields));

        $retval = [];
        if ($results) {
            foreach ($results as $k => $result) {
                $retval[$oriFields[$k]] = self::decode($result);
            }
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $retval;
    }

    public static function hSet($configKey, $keys, $field='', $value='')
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
        $result = (yield $redis->hSet($realKey, $field, $value));
        
        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl);
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hMSet($configKey, $keys, array $kv)
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

        $params = [];
        foreach ($kv as $k => $v) {
            $params[] = $k;
            $params[] = $v;
        }
        $result = (yield $redis->hMSet($realKey, ...$params));

        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl);
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }
    
    public static function hExists($configKey, $keys, $field = '')
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
        $result = (yield $redis->hExists($realKey, $field));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hGetAll($configKey, $keys)
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
        $result = (yield $redis->hGetAll($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hKeys($configKey, $keys)
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
        $result = (yield $redis->hKeys($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }
    
    public static function hDel($configKey, $keys)
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
        $result = (yield $redis->hDel($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }
    
    /* hash ops end  ****************************************/

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

        if (isset($config['encode']) && $config['encode'] == 'gz') {
            $value = gzencode($value, 1);
        }
        $result = (yield $redis->set($realKey, $value));
        
        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl);
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function incr($configKey, $keys)
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

        $result = (yield $redis->incr($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function mGet($configKey, array $keysArr)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }

        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));

        $redis = new Redis($conn);

        $realKeys = [];
        foreach ($keysArr as $keys) {
            $realKeys[] = self::getRealKey($config, $keys);
        }

        $result = (yield $redis->mget(...$realKeys));

        $gz = isset($config['encode']) && $config['encode'] == 'gz';
        if ($result && $gz) {
            foreach ($result as &$item) {
                if ($item !== null) {
                    $item = gzdecode($item);
                }
            }
            unset($item);
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function mSet($configKey, array $keysArr, array $values)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config) || count($keysArr) !== count($values) || empty($values)) {
            yield false;
            return;
        }
        $keysArr = array_values($keysArr);
        $values = array_values($values);

        $redisObj = self::init($config['connection']);
        $conn = (yield $redisObj->getConnection($config['connection']));
        $redis = new Redis($conn);

        $gz = isset($config['encode']) && $config['encode'] == 'gz';
        $ttl = isset($config['exp']) ? $config['exp'] : 0;

        if ($ttl) {
            $result = true;
            foreach ($keysArr as $i => $keys) {
                $realKey = self::getRealKey($config, $keys);
                $value = $gz ? gzencode($values[$i], 1) : $values[$i];
                $result = $result && (yield $redis->setex($realKey, $ttl, $value));
            }
        } else {
            $args = [];
            foreach ($keysArr as $i => $keys) {
                $args[] = self::getRealKey($config, $keys);
                $args[] = $gz ? gzencode($values[$i], 1) : $values[$i];
            }
            $result = (yield $redis->mset(...$args));
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    private static function expire($redis, $key, $ttl=0)
    {
        if(!$ttl || !$key){
            yield false;
            return;
        }

        yield $redis->expire($key, $ttl);
    }

    /**
     * @param $connection
     * @return \Generator
     * @throws ZanException
     */
    public function getConnection($connection)
    {
        $conn = (yield ConnectionManager::getInstance()->get($connection));
        if (!$conn instanceof Connection) {
            throw new ZanException('Redis get connection error');
        }
        yield $this->insertActiveConnectionIntoContext($conn);
        yield $conn;
    }

    private function insertActiveConnectionIntoContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            $activeConnections = new ObjectArray();
        }
        $activeConnections->push($connection);
        yield setContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, $activeConnections);
    }

    private static function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        $activeConnections->remove($connection);
    }

    private static function closeActiveConnectionFromContext()
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        while (!$activeConnections->isEmpty()) {
            $connection = $activeConnections->pop();
            if ($connection instanceof Connection) {
                $connection->close();
            }
        }
    }

    public static function terminate()
    {
        yield self::closeActiveConnectionFromContext();
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

        return sprintf($format, ...array_values($keys));
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
        } elseif (preg_match('/^\s*[\[|\{].*[\]|\}\s*$]/',$value)){
            $value = json_decode($value,true);
        }

        return $value;
    } 
}