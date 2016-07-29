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
use Zan\Framework\Network\Server\Timer\Timer;

class Redis implements ConnectionFactory
{
    const CONNECT_TIMEOUT = 30000;

    /**
     * @var array
     */
    private $config;
    private $socket;
    /**
     * @var Client
     */
    private $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function create()
    {
        $this->socket = new SwooleRedis();
        $this->connection = new Client();
        $this->connection->setSocket($this->socket);
        $this->connection->setConfig($this->config);
        $this->connection->init();

        //call connect
        $this->socket->connect($this->config['host'], $this->config['port'], [$this->connection, 'onConnect']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, [$this, 'connectTimeout'], $this->connection->getConnectTimeoutJobId());

        return $this->connection;
    }

    public function close()
    {
    }

    public function connectTimeout()
    {
        $this->socket = new SwooleRedis();
        $this->connection->setSocket($this->socket);
        $this->connection->init();
        $this->socket->connect($this->config['host'], $this->config['port'], [$this->connection, 'onConnect']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, [$this, 'connectTimeout'], $this->connection->getConnectTimeoutJobId());
    }
}