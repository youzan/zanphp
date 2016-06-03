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

    private $nowReloadStepTime = 0;
    private $incReloadStepTime = 5000;
    private $maxReloadStepTime = 30000;

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
        $this->createConnections();
        $this->initLoadBalancingStrategy();
    }

    private function createConnections()
    {
        foreach ($this->config['connections'] as $config) {
            $this->createConnection($config);
        }
    }

    public function createConnection($config)
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

    public function getConnectionByHostPort($host, $port)
    {
        foreach ($this->connections as $connection) {
            $config = $connection->getConfig();
            if ($config['host'] == $host && $config['port'] == $port) {
                return $connection;
            }
        }
        return null;
    }

    public function get($serviceName)
    {
        yield $this->loadBalancingStrategy->get($serviceName);
    }

    public function reload(array $config)
    {
        $this->createConnection($config);
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