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
    private $socket;

    /**
     * @var NovaClientConnection
     */
    private $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $clientFlags = SWOOLE_SOCK_TCP;
        $this->socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
        $this->socket->set($this->config['config']);

        $this->connection = new NovaClientConnection();
        $this->connection->setSocket($this->socket);
        $this->connection->setConfig($this->config);
        $this->connection->init();

        //call connect
        $this->socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, [$this, 'connectTimeout'], $this->connection->getConnectTimeoutJobId());

        return $this->connection;
    }

    public function connectTimeout()
    {
        $clientFlags = SWOOLE_SOCK_TCP;
        $this->socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
        $this->socket->set($this->config['config']);

        $this->connection->setSocket($this->socket);
        $this->connection->init();
        $this->socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, [$this, 'connectTimeout'], $this->connection->getConnectTimeoutJobId());
    }

    public function close()
    {

    }

}
