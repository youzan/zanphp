<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/4/21
 * Time: 上午11:48
 */

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;
use swoole_client as SwooleClient;
use Zan\Framework\Network\Connection\Driver\NovaClient as NovaClientConnection;
use Zan\Framework\Network\Server\Timer\Timer;

class NovaClient implements ConnectionFactory
{
    const CONNECT_TIMEOUT = 30000;

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

        $connection = new NovaClientConnection();
        $connection->setSocket($socket);
        $connection->setConfig($this->config);
        $connection->init();

        //call connect
        $socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());

        return $connection;
    }

    public function getConnectTimeoutCallback(NovaClientConnection $connection)
    {
        return function() use ($connection) {
            $connection->getSocket()->close();
            $connection->unsetSocket();
            
            $clientFlags = SWOOLE_SOCK_TCP;
            $socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
            $socket->set($this->config['config']);

            $connection->setSocket($socket);
            $connection->init();
            $socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
            Timer::after($connectTimeout, $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());
        };
    }

    public function close()
    {

    }

}
