<?php

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;
use swoole_client as SwooleClient;
use Zan\Framework\Network\Connection\Driver\NovaClient as NovaClientConnection;
use Zan\Framework\Network\Server\Timer\Timer;

class NovaClient implements ConnectionFactory
{
    const CONNECT_TIMEOUT = 3000;

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $clientFlags = SWOOLE_SOCK_TCP;
        $socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
        $socket->set($this->config['config']);

        $serverInfo = isset($this->config["server"]) ? $this->config["server"] : [];
        $connection = new NovaClientConnection($serverInfo);
        $connection->setSocket($socket);
        $connection->setConfig($this->config);
        $connection->init();

        //call connect
        if (false === $socket->connect($this->config['host'], $this->config['port'])) {
            sys_error("NovaClient connect ".$this->config['host'].":".$this->config['port']. " failed");
            return null;
        }

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());

        return $connection;
    }

    public function getConnectTimeoutCallback(NovaClientConnection $connection)
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
