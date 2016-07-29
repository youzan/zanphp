<?php

namespace Zan\Framework\Store\NoSQL\KV;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Utilities\Encode\LZ4;

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

    const DEFAULT_BIN_NAME = '_z_dft';
    const AEROSPIKE_OK = 'AEROSPIKE_OK';

    //压缩阀值
    const COMPRESS_LEN = 1024;
   

    public function __construct($namespace, $setName, $connection)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
        $this->conn = $connection;
        $this->policy = $connection->getConfig()['policy'];
    }

    public function set($key, $binName, $value, $ttl = 0)
    {
        $this->policy['ttl'] = $ttl;

        if (is_string($value) && strlen($value) > self::COMPRESS_LEN) {
            $value = LZ4::getInstance()->encode($value);
        }

        $binList = [$binName => $value];
        $this->conn->getSocket()->put_async(
            $this->namespace,
            $this->setName,
            $key,
            $binList,
            [$this, 'writeCallBack'],
            $this->policy
        );
        
        yield $this;
    }

    public function setMulti($key, array $binList, $ttl = 0)
    {
        $this->policy['ttl'] = $ttl;

        foreach ($binList as $binName => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }

            if (strlen($value) > self::COMPRESS_LEN) {
                $binList[$binName] = LZ4::getInstance()->encode($value);
            }
        }

        $this->conn->getSocket()->put_async(
            $this->namespace,
            $this->setName,
            $key,
            $binList,
            [$this, 'writeCallBack'],
            $this->policy
        );

        yield $this;
    }

    public function incr($key, $value, $binName = null)
    {
        $binName = (null === $binName) ? self::DEFAULT_BIN_NAME : $binName;
        $this->conn->getSocket()->incr_async(
            $this->namespace,
            $this->setName,
            $key,
            $binName,
            $value,
            [$this, 'writeCallBack'],
            $this->policy
        );

        yield $this;
    }

    public function get($key, $binName = null)
    {
        $binName = (null === $binName) ? [self::DEFAULT_BIN_NAME] : [$binName];
        $this->conn->getSocket()->key_select_async(
            $this->namespace,
            $this->setName,
            $key,
            $binName,
            [$this, 'readCallBack'],
            $this->policy
        );

        yield $this;
    }

    public function getMulti($key, array $binNameList)
    {
        $this->conn->getSocket()->key_select_async(
            $this->namespace,
            $this->setName,
            $key,
            $binNameList,
            [$this, 'readMultiCallBack'],
            $this->policy
        );

        yield $this;
    }

    public function getAll($key)
    {
        $this->conn->getSocket()->get_async(
            $this->namespace,
            $this->setName,
            $key,
            [$this, 'readMultiCallBack'],
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
        if ($err == self::AEROSPIKE_OK) {
            call_user_func($this->callback, true);
        } else {
            //TODO: 日志记录err
            call_user_func($this->callback, false);
        }
    }

    public function readCallBack($err, $rec)
    {
        if ($err != self::AEROSPIKE_OK) {
            //TODO: 日志记录err
            call_user_func($this->callback, null);
            return;
        }

        $LZ4 = LZ4::getInstance();

        if (!is_array($rec) || count($rec) !== 1) {
            //TODO: 记录返回值异常
            call_user_func($this->callback, null);
            return;
        }

        $ret = current($rec);
        if ($LZ4->isLZ4($ret)) {
            $ret = $LZ4->decode($ret);
        }
        call_user_func($this->callback, $ret);
    }

    public function readMultiCallBack($err, $rec)
    {
        if ($err != self::AEROSPIKE_OK) {
            //TODO: 日志记录err
            call_user_func($this->callback, null);
            return;
        }

        $LZ4 = LZ4::getInstance();

        if (!is_array($rec)) {
            //TODO: 记录返回值异常
            call_user_func($this->callback, null);
            return;
        }

        foreach ($rec as $binName => $value) {
            if ($LZ4->isLZ4($value)) {
                $rec[$binName] = $LZ4->decode($value);
            }
        }
        call_user_func($this->callback, $rec);
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }
}