<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 13:03
 */

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;
use \Zan\Framework\Network\Connection\Driver\Redis as Client;
use Zan\Framework\Store\NoSQL\Redis\RedisClient;

class Redis implements ConnectionFactory
{
    /**
     * @var array
     */
    private $config;
    private $conn;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function create()
    {
        $this->conn = new RedisClient($this->config['host'], $this->config['port']);
        $redis = new Client();
        $redis->setSocket($this->conn);
        $redis->setConfig($this->config);
        return $redis;
    }

    public function close()
    {
    }

}