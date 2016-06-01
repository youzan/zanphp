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

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $connections = [];
        foreach ($this->config as $config) {
            $clientFlags = SWOOLE_SOCK_TCP;
            $socket = new SwooleClient($clientFlags, SWOOLE_SOCK_ASYNC);
            $socket->set($config['config']);

            $connection = new \Zan\Framework\Network\Connection\Driver\NovaClient();
            $connection->setSocket($socket);
            $connection->setConfig($config);
            $connection->init();

            //call connect
            $socket->connect($config['host'], $config['port'], $config['timeout']);
            $connections[] = $connection;
        }
        return $connections;
    }

    public function close()
    {

    }

}
