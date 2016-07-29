<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 13:03
 */

namespace Zan\Framework\Network\Connection\Factory;

use swoole_client as SwooleClient;
use Zan\Framework\Contract\Network\ConnectionFactory;
use Zan\Framework\Network\Connection\Driver\Syslog as SyslogDriver;
use Zan\Framework\Network\Server\Timer\Timer;

class Syslog implements ConnectionFactory
{
    const CONNECT_TIMEOUT = 30000;

    /**
     * @var array
     */
    private $config;
    private $socket;

    /**
     * @var SyslogDriver
     */
    private $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $this->socket = new SwooleClient(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $this->connection = new SyslogDriver();
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
        $this->socket = new SwooleClient(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $this->connection->setSocket($this->socket);
        $this->connection->init();
        $this->socket->connect($this->config['host'], $this->config['port'], $this->config['timeout']);

        $connectTimeout = isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT;
        Timer::after($connectTimeout, [$this, 'connectTimeout'], $this->connection->getConnectTimeoutJobId());
    }

    public function close()
    {
        $this->socket->close();
    }

}
