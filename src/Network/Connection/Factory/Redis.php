<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 13:03
 */

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;
use swoole_redis as SwooleRedis;
use \Zan\Framework\Network\Connection\Driver\Redis as Client;

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
        $this->conn = new SwooleRedis();
        $connection = new Client();
        $connection->setSocket($this->conn);
        $connection->setConfig($this->config);
        $connection->init();

        //call connect
        $this->conn->connect($this->config['host'], $this->config['port'], [$connection, 'onConnect']);
        return $connection;
    }

    public function close()
    {
    }
}