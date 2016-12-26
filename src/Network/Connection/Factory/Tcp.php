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
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Connection\Driver\Tcp as TcpConnection;

class Tcp implements ConnectionFactory
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $isUnixSock = isset($this->config['path']);

        $clientFlags = $isUnixSock ? SWOOLE_SOCK_UNIX_STREAM : SWOOLE_SOCK_TCP;
        $socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
        $socket->set($this->config['config']);

        $connection = new TcpConnection();
        $connection->setSocket($socket);
        $connection->setConfig($this->config);
        $connection->init();

        //call connect
        if ($isUnixSock) {
            $socket->connect($this->config['path']);
        } else {
            $socket->connect($this->config['host'], $this->config['port']);
        }

        Timer::after($this->config['connect_timeout'], $this->getConnectTimeoutCallback($connection), $connection->getConnectTimeoutJobId());

        return $connection;
    }

    public function getConnectTimeoutCallback(TcpConnection $connection)
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
