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

class Syslog implements ConnectionFactory
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
        $this->conn = new SwooleClient(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $connection = new SyslogDriver();
        $connection->setSocket($this->conn);
        $connection->setConfig($this->config);
        $connection->init();

        //call connect
        $this->conn->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        return $connection;
    }

    public function close()
    {
        $this->conn->close();
    }

}
