<?php

namespace Zan\Framework\Store\NoSQL\KV;
use Zan\Framework\Foundation\Contract\Async;

/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/5
 * Time: 上午10:06
 */

class KVStore implements Async
{
    private $namespace;
    private $setName;
    private $conn;
    private $policy;

    private $callback;

    const DEFAULT_BIN_NAME = 'z_dft';
    const AEROSPIKE_OK = 'AEROSPIKE_OK';

    public function __construct($namespace, $setName, $connection)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
        $this->conn = $connection;
        $this->policy = $connection->getConfig()['policy'];
    }

    public function set($key, $value, $ttl = 0)
    {
        $this->policy['ttl'] = $ttl;

        $this->conn->getSocket()->put_simple_async(
            $this->namespace,
            $this->setName,
            $key,
            self::DEFAULT_BIN_NAME,
            $value,
            [$this, 'writeCallBack'],
            $this->policy
        );

        yield $this;
    }

    public function get($key)
    {
        $this->conn->getSocket()->get_async(
            $this->namespace,
            $this->setName,
            $key,
            [$this, 'readCallBack'],
            $this->policy
        );
        yield $this;
    }

    public function remove($key)
    {
        $this->conn->getSocket()->key_remove_async(
            $this->namespace,
            $this->setName,
            $key,
            [$this, 'writeCallBack'],
            $this->policy
        );
        yield $this;
    }

    public function writeCallback($err)
    {
        $this->conn->release();
        if ($err == self::AEROSPIKE_OK) {
            call_user_func($this->callback, true);
        } else {
            //TODO: 日志记录err
            call_user_func($this->callback, false);
        }
    }

    public function readCallBack($err, $rec)
    {
        $this->conn->release();
        if ($err != self::AEROSPIKE_OK) {
            //TODO: 日志记录err
            call_user_func($this->callback, null);
        }

        //set的情况
        if (isset($rec[self::DEFAULT_BIN_NAME])) {
            call_user_func($this->callback, $rec[self::DEFAULT_BIN_NAME]);
        } else {
            call_user_func($this->callback, $rec);
        }
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }
}