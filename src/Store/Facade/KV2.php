<?php

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception;
use Zan\Framework\Store\NoSQL\Redis\Redis as KVRedis;
use Zan\Framework\Utilities\Encode\LZ4;
use Zan\Framework\Utilities\Types\ObjectArray;

/**
 * Class KVRedis
 *
 * 具体命令支持 参考 doc
 * http://doc.qima-inc.com/pages/viewpage.action?pageId=4860611
 *
 * AS与REDIS协议映射关系参考 :
 *
 * AS                  | REDIS
 * --------------------|------------
 * namespace:set:{key} | hash key
 * bin                 | hash field
 * {value}             | hash value
 *
 * set ns:set:key def_bin value
 * get ns:set:key def_bin
 *
 * hset ns:set:key bin value
 * hget ns:set:key bin
 */
class KV2 {

    const COMPRESS_LEN = 1024; /* lz4 压缩阈值(min:strlen) */
    const DEFAULT_BIN_NAME = '_z_dft';
    const ACTIVE_CONNECTION_CONTEXT_KEY= 'kv_store2_active_connections';

    private static $_instance = null;
    private static $_configMap = null;

    private $namespace;
    private $setName;

    private function __construct($namespace, $setName)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
    }

    /**
     * @return static
     */
    private static function getIns($config)
    {
        $ns = $config['namespace'];
        $set = $config['set'];

        $key = "$ns:$set";

        if (null === self::$_instance[$key]) {
            self::$_instance[$key] = new static($ns, $set);
        }
        return self::$_instance[$key];
    }

    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }

    public static function get($configKey, $fmtArgs)
    {
        yield self::hGet($configKey, $fmtArgs, self::DEFAULT_BIN_NAME);
    }

    public static function set($configKey, $fmtArgs, $value)
    {
        yield self::hSet($configKey, $fmtArgs, self::DEFAULT_BIN_NAME, $value);
    }

    public static function hGet($configKey, $fmtArgs, $bin)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->hGet($realKey, $bin));
        $result = self::unSerialize($result);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hSet($configKey, $fmtArgs, $bin, $value)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $value = self::serialize($value);

        $result = (yield $redis->hSet($realKey, $bin, $value));

        $ttl = isset($conf['exp']) ? $conf['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result === 1;
    }

//    public static function setex() {}
//    public static function exists() {}
//    public static function mGet($configKey, array $fmtArgsArray)
//    {
//        /* @var Connection $conn */
//        $conf = self::getItemConfig($configKey);
//        $self = self::getIns($conf);
//
//        $conn = (yield $self->getConnection($conf['connection']));
//        $redis = new KVRedis($conn);
//
//        $realKeys = [];
//        foreach ($fmtArgsArray as $fmtArgs) {
//            $realKeys[] = $self->fmtKVKey($conf, $fmtArgs);
//        }
//        $resultList = (yield $redis->mGet(...$realKeys));
//        if ($resultList) {
//            foreach ($resultList as &$result) {
//                if ($result !== null) {
//                    $result = self::unSerialize($result);
//                }
//            }
//            unset($result);
//        }
//
//        /** @noinspection PhpVoidFunctionResultUsedInspection */
//        yield self::deleteActiveConnectionFromContext($conn);
//        $conn->release();
//
//        yield $resultList;
//    }

    // redis 协议支持有限制
    private static function __callStatic($func, $args)
    {
        /* @var Connection $conn */
        $configKey = array_shift($args);
        $keys = array_shift($args);

        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $keys);
        $result = (yield $redis->$func($realKey, ...$args));

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    private static function expire(KVRedis $redis, $key, $ttl = 0)
    {
        /* @var Connection $conn */
        if(!$ttl || !$key){
            yield false;
            return;
        }

        yield $redis->expire($key, $ttl);
    }

    public function getConnection($connection)
    {
        $conn = (yield ConnectionManager::getInstance()->get($connection));
        if (!$conn instanceof Connection) {
            throw new Exception('kv get connection error');
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

    public static function serialize($payload)
    {
        if (is_scalar($payload)) {
            $payload = strval($payload);
        } else {
            $payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($payload === false) {
                $errno = json_last_error();
                throw new InvalidArgumentException("serialize kv payload fail, [errno=$errno]");
            }
        }

        if (strlen($payload) > self::COMPRESS_LEN) {
            $payload = LZ4::getInstance()->encode($payload);
        }
        return $payload;
    }

    public static function unSerialize($payload)
    {
        if ($payload === null) {
            return null;
        }

        $lz4 = LZ4::getInstance();
        if ($lz4->isLZ4($payload)) {
            $payload = $lz4->decode($payload);
        }
        return $payload;
    }

    private function fmtKVKey($config, $fmtArgs){
        $kvPrefix = "$this->namespace:$this->setName:";

        $format = isset($config['key']) ? $config['key'] : null ;

        if($fmtArgs === null){
            if ($format === null) {
                throw new InvalidArgumentException('expect keys is string or array, null given');
            }
            return $kvPrefix . $format;
        } else {
            if(!is_array($fmtArgs)){
                $fmtArgs = [$fmtArgs];
            }
            return $kvPrefix . sprintf($format, ...$fmtArgs);
        }
    }

    /**
     * @param string $configKey
     * @return array
     * @throws InvalidArgumentException
     */
    private static function getItemConfig($configKey)
    {
        $result = self::$_configMap;
        $routes = explode('.', $configKey);
        if (empty($routes)) {
            throw new InvalidArgumentException("Empty KV configKey");
        }
        foreach ($routes as $route) {
            if (!isset($result[$route])) {
                throw new InvalidArgumentException("Invalid KV config [configKey=$configKey]");
            }
            $result = &$result[$route];
        }
        return self::validConfig($configKey, $result);
    }

    private static function validConfig($configKey, $config)
    {
        if (!$config
            || !isset($config['connection'])
            || !isset($config['key'])
            || !isset($config['namespace'])
            || !isset($config['set'])
        ) {
            throw new InvalidArgumentException("Invalid KV config [configKey=$configKey]");
        }
        return $config;
    }

}