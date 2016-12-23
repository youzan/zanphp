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
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function create()
    {
        $socket = new SwooleRedis();
        $connection = new Client();
        $connection->setSocket($socket);
        $connection->setConfig($this->config);
        $connection->init();

        $isUnixSock = is_readable($this->config['host']);
        if ($isUnixSock) {
            $socket->connect($this->config['host'], null, [$connection, 'onConnect']);
        } else {
            $socket->connect($this->config['host'], $this->config['port'], [$connection, 'onConnect']);
        }

        Timer::after($this->config['connect_timeout'], $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());

        return $connection;
    }

    public function getConnectTimeoutCallback(Client $connection)
    {
        return function() use ($connection) {
            $connection->close();
            $connection->onConnectTimeout();
        };
    }

    public function close()
    {
    }

}