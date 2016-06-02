<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:40
 */
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;
use Zan\Framework\Contract\Network\Connection;

class NovaClientPool
{
    private $connections = [];

    private $config;

    private $loadBalancingStrategyMap = [
        'polling' => 'Zan\Framework\Network\Connection\LoadBalancingStrategy\Polling',
    ];

    /**
     * @var LoadBalancingStrategyInterface
     */
    private $loadBalancingStrategy;

    public function __construct(array $config)
    {
        $this->init($config);
    }

    private function init($config)
    {
        $this->config = $config;
        $this->createConnect();
        $this->initLoadBalancingStrategy();
    }

    private function createConnect()
    {
        foreach ($this->config['connections'] as $config) {
            $novaClientFactory = new NovaClientFactory($config);
            $connection = $novaClientFactory->create();
            if ($connection instanceof Connection) {
                $key = spl_object_hash($connection);
                $this->connections[$key] = $connection;
                $connection->setPool($this);
                $connection->heartbeat();
            }
        }
    }

    private function initLoadBalancingStrategy()
    {
        $key = $this->config['strategy'];
        if (!isset($this->loadBalancingStrategyMap[$key])) {
            //exception
        }
        $loadBalancingStrategy = $this->loadBalancingStrategyMap[$key];
        $this->loadBalancingStrategy = new $loadBalancingStrategy($this);
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function get()
    {
        yield $this->loadBalancingStrategy->get();
    }

    public function reload(array $config)
    {
        $novaClientFactory = new NovaClientFactory($config);
        $connection = $novaClientFactory->create();
        if ($connection instanceof Connection) {
            $key = spl_object_hash($connection);
            $this->connections[$key] = $connection;
            $connection->setPool($this);
            $connection->heartbeat();
        }
    }

    public function remove(Connection $conn)
    {
        $key = spl_object_hash($conn);
        if (!isset($this->connections[$key])) {
            return false;
        }
        unset($this->connections[$key]);
    }

    public function recycle(Connection $conn)
    {

    }
}