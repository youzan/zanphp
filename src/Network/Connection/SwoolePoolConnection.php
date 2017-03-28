<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/28
 * Time: 上午11:07
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\Connection;

class SwoolePoolConnection implements Connection
{
    public $conn;
    public $pool;

    public function __construct($conn, SwooleConnectionPool $pool)
    {
        $this->pool = $pool;
    }

    public function getSocket()
    {
        return $this->conn;
    }

    public function release()
    {
        $this->pool->release($this->conn);
    }

    public function getEngine()
    {
        return $this->pool->poolType;
    }

    public function getConfig()
    {
        // $config['timeout']
        // $config['pool']['pool_name']
        return $this->pool->config;
    }

    public function heartbeat() { }
    public function close() { }
}