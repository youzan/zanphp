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
use \Zan\Framework\Network\Connection\Driver\KVStore as Client;

class KVStore implements ConnectionFactory
{
    private $config;
    private $conn;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $this->conn = new \swoole_aerospike();

        $hosts = '';
        if (is_array($this->config['ip_list'])) {
            $hosts = implode(';', $this->config['ip_list']);
        } else {
            $hosts = $this->config['ip_list'];
        }

        $this->conn->init($this->config['user'], $this->config['password'], $hosts);

        $connection = new Client();
        $connection->setSocket($this->conn);
        $connection->setConfig($this->config);

        //call connect
        if (!$this->conn->connect()) {
            //TODO: 链接失败
            echo "KVStore connect error \n";
        }
        return $connection;
    }
    

    public function close()
    {
        $this->conn->close();
    }

}