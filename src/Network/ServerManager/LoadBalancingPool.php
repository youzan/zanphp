<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:40
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Contract\Network\ConnectionPool;
use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Contract\Network\ConnectionFactory;

class LoadBalancingPool implements ConnectionPool
{
    private $connections = [];

    /**
     * @var ConnectionFactory
     */
    private $factory;

    private $config;

    private $strategyMap = [
        'polling' => 'Zan\Framework\Network\ServerManager\LoadBalancingStrategy\Polling',
    ];

    /**
     * @var LoadBalancingStrategyInterface
     */
    private $strategy;

    public function __construct(ConnectionFactory $connectionFactory, array $config, $type)
    {
        $this->init($connectionFactory, $config);
    }

    private function init($connectionFactory, $config)
    {
        $this->config = $config;
        $this->factory = $connectionFactory;
        $this->createConnect();
        $this->initStrategy();
    }

    private function createConnect()
    {
        $connections = $this->factory->create();
        foreach ($connections as $connection) {
            if ($connection instanceof Connection) {
                $key = spl_object_hash($connection);
                $this->connections[$key] = $connection;
                $connection->setPool($this);
                $connection->heartbeat();
            }
        }
    }

    private function initStrategy()
    {
        $key = $this->config['strategy'];
        if (!isset($this->strategyMap[$key])) {
            //exception
        }
        $strategy = $this->strategyMap[$key];
        $this->strategy = new $strategy($this);
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function get()
    {
        yield $this->strategy->get();
    }



    public function reload(array $config)
    {

    }

    public function remove(Connection $conn)
    {

    }

    public function recycle(Connection $conn)
    {

    }
}