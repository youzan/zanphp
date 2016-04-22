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

class NovaClient implements ConnectionFactory
{
    private $config;
    private $conn;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        //$clientFlags = $this->config['persistent'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_SOCK_TCP;
        //咨询了swoole的作者,得知swoole_keep只是为了php-fpm设计的,并不适用于我们的cli常驻内存模型(蚩尤注)
        $clientFlags = SWOOLE_SOCK_TCP;
        $this->conn = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
        $this->conn->set($this->config['config']);

        $connection = new \Zan\Framework\Network\Connection\Driver\NovaClient();
        $connection->setSocket($this->conn);
        $connection->setConfig($this->config);
        $connection->setIsAsync(true);
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