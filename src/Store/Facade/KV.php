<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/4
 * Time: 下午6:35
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception;
use Zan\Framework\Store\NoSQL\KV\KVStore;
use Zan\Framework\Utilities\Types\ObjectArray;

class KV
{
    const POOL_PREFIX = 'connection.kvstore.';

    const ACTIVE_CONNECTION_CONTEXT_KEY= 'kv_store_active_connections';

    private $namespace;
    private $setName;

    private static $_configMap = null;
    private static $_instance = null;


    /**
     * @param $namespace
     * @param $setName
     * @return mixed
     */
    private static function init($namespace, $setName)
    {
        if (null === self::$_instance[$namespace]) {
            self::$_instance[$namespace] = new KV($namespace, $setName);
        }
        return self::$_instance[$namespace];
    }

    /**
     * KV constructor.
     * @param $namespace
     * @param $setName
     */
    private function __construct($namespace, $setName)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
    }

    /**
     * @param $configMap
     */
    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }

    /**
     * @param $configKey
     * @param $keys
     * @param string|int|double $value
     * @return \Generator|void
     */
    public static function set($configKey, $keys, $value)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        $set = (yield $kv->set($realKey, KVStore::DEFAULT_BIN_NAME, $value, $ttl));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $set;
    }

    /**
     * @param $configKey
     * @param $keys
     * @param $binName
     * @param $value
     * @return \Generator|void
     * @throws InvalidArgumentException
     */
    public static function hSet($configKey, $keys, $binName, $value)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        $hSet = (yield $kv->set($realKey, $binName, $value, $ttl));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $hSet;
    }

    /**
     * @param $configKey
     * @param $keys
     * @param array $binList
     * @return \Generator|void
     * @throws InvalidArgumentException
     */
    public static function hMSet($configKey, $keys, array $binList)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $ttl = isset($config['exp']) ? $config['exp'] : 0;
        $hMSet = (yield $kv->setMulti($realKey, $binList, $ttl));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $hMSet;
    }

    /**
     * @param $configKey
     * @param $keys
     * @param null $binName
     * @param int $value
     * @return \Generator|void
     */
    public static function incr($configKey, $keys, $binName = null, $value = 1)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $incr = (yield $kv->incr($realKey, $value, $binName));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $incr;
    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     * @throws InvalidArgumentException
     */
    public static function get($configKey, $keys)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $get = (yield $kv->get($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $get;
    }

    /**
     * @param $configKey
     * @param $keys
     * @param $binName
     * @return \Generator|void
     * @throws InvalidArgumentException
     */
    public static function hGet($configKey, $keys, $binName)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $hGet = (yield $kv->get($realKey, $binName));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $hGet;
    }

    public static function hMGet($configKey, $keys, array $binNameList)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $hMGet = (yield $kv->getMulti($realKey, $binNameList));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $hMGet;
    }

    public static function hGetAll($configKey, $keys)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $hGetAll = (yield $kv->getAll($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $hGetAll;
    }

        /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function remove($configKey, $keys)
    {
        $config = self::getConfigCacheKey($configKey);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $realKey = self::getRealKey($config, $keys);

        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        $remove = (yield $kv->remove($realKey));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $remove;
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
            throw new Exception('KV get connection error');
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

    private function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        $activeConnections->remove($connection);
    }

    private function closeActiveConnectionFromContext()
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
            || !isset($config['namespace'])
            || !isset($config['set'])) {
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
}