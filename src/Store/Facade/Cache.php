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

    const ACTIVE_CONNECTION_CONTEXT_KEY= 'redis_active_connections';

    private static $_instance = null;
    private static $_configMap = null;

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

        $result = (yield call_user_func_array([$redis, $func], $args));

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
        $result = self::decode($result);

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

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

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        if ($result) {
            $conn = (yield $redisObj->getConnection($config['connection']));
            $redis = new Redis($conn);
            $ttl = isset($config['exp']) ? $config['exp'] : 0;
            yield $redis->expire($realKey, $ttl);

            yield self::deleteActiveConnectionFromContext($conn);
            $conn->release();
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
        yield $this->insertActiveConnectionIntoContext($conn);
        yield $conn;
    }

    private function insertActiveConnectionIntoContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, []));
        $activeConnections[spl_object_hash($connection)] = $connection;
        yield setContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, $activeConnections);
    }

    private static function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, []));
        if (isset($activeConnections[spl_object_hash($connection)])) {
            unset($activeConnections[spl_object_hash($connection)]);
        }
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
     * !!!这是一个为了兼容Iron的过渡方案, Iron废弃后需要移除!!!
     * @param $value
     * @return mixed
     */
    private static function decode($value)
    {
        if (($ret = json_decode($value, true)) === NULL) {
            $ret = $value;
        }

        return $ret;
    }
}