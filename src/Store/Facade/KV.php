<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/4
 * Time: 下午6:35
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception;
use Zan\Framework\Store\NoSQL\KV\KVStore;

class KV
{
    const DELIMITER = '.';
    private $namespace;
    private $setName;

    private static $_instance = null;


    final public static function getInstance($namespace)
    {
        if (null === self::$_instance[$namespace]) {
            self::$_instance[$namespace] = new KV($namespace);
        }
        return self::$_instance[$namespace];
    }

    /**
     * KV constructor.
     * namespace example: dafault.tablename
     * @param $namespace
     */
    public function __construct($namespace)
    {
        if (false === stripos($namespace, self::DELIMITER)) {
            return false;
        }

        list($this->namespace, $this->setName) = explode(self::DELIMITER, $namespace);
    }

    /**
     * @param $key
     * @param $value
     * @param int $ttl
     * @return \Generator
     * @throws Exception
     */
    public function set($key, $value, $ttl = 0)
    {
        $conn = (yield $this->getConnection());
        $kv = new KVStore($this->namespace, $this->setName, $conn);
        yield $kv->set($key, $value, $ttl);
    }

    public function setList($key, array $value, $ttl = 0)
    {
        $conn = (yield $this->getConnection());
        $kv = new KVStore($this->namespace, $this->setName, $conn);
        yield $kv->setList($key, $value, $ttl);
    }

    public function setMap($key, array $value, $ttl = 0)
    {
        $conn = (yield $this->getConnection());
        $kv = new KVStore($this->namespace, $this->setName, $conn);
        yield $kv->setMap($key, $value, $ttl);
    }

    /**
     * @param $key
     * @return \Generator
     * @throws Exception
     */
    public function get($key)
    {
        $conn = (yield $this->getConnection());
        $kv = new KVStore($this->namespace, $this->setName, $conn);
        yield $kv->get($key);
    }

    /**
     * @param $key
     * @return \Generator
     * @throws Exception
     */
    public function remove($key)
    {
        $conn = (yield $this->getConnection());
        $kv = new KVStore($this->namespace, $this->setName, $conn);
        yield $kv->remove($key);
    }

    /**
     * @return \Generator
     * @throws Exception
     * @throws \Zan\Framework\Foundation\Exception\System\InvalidArgumentException
     */
    public function getConnection()
    {
        $conn = (yield ConnectionManager::getInstance()->get($this->namespace));
        if (!$conn instanceof Connection) {
            throw new Exception('KV get connection error');
        }

        yield $conn;
    }
}