<?php


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
        /** @noinspection PhpUndefinedFieldInspection */
        $socket->isClosed = null;
        $connection = new Client();
        $connection->setSocket($socket);
        $connection->setConfig($this->config);
        $connection->init();

        $isUnixSock = isset($this->config["path"]);
        if ($isUnixSock) {
            $result = $socket->connect($this->config['path'], null, [$connection, 'onConnect']);
            $dst = $this->config['path'];
        } else {
            $result = $socket->connect($this->config['host'], $this->config['port'], [$connection, 'onConnect']);
            $dst = $this->config['host'].":".$this->config['port'];
        }
        if (false === $result) {
            sys_error("Redis connect $dst failed");
            return null;
        }

        Timer::after($this->config['connect_timeout'], $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());

        return $connection;
    }

    public function getConnectTimeoutCallback(Client $connection)
    {
        return function() use ($connection) {
            $connection->close();
            /** @noinspection PhpUndefinedFieldInspection */
            $connection->getSocket()->isClosed = true;
            $connection->onConnectTimeout();
        };
    }

    public function close()
    {
    }

}