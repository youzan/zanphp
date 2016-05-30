<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:40
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class LoadBalancingPool
{
    private $connections = [];

    private $strategyMap = [
        'polling' => 'Zan\Framework\Network\ServerManage\LoadBalancingStrategy\Polling',
    ];

    /**
     * @var LoadBalancingStrategyInterface
     */
    private $strategy;

    private $config;

    public function __construct($config)
    {
        $this->init($config);
    }

    private function init($config)
    {
        $this->config = $config;
        $this->createConnect();
        $this->initStrategy();
    }

    private function createConnect()
    {
        foreach ($this->config['connections'] as $config) {
            $novaClientFactory = new NovaClientFactory($config);
            $connection = $novaClientFactory->create();
            $connection->heartbeat();
            $key = spl_object_hash($connection);
            $this->connections[$key] = $connection;
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
}