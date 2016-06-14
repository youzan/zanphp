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
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Connection\Exception\CanNotFindLoadBalancingStrategeMapException;

class NovaClientPool
{
    private $connections = [];

    private $waitingConnections = [];

    private $config;

    private $loadBalancingStrategyMap = [
        'polling' => 'Zan\Framework\Network\Connection\LoadBalancingStrategy\Polling',
    ];

    const CONNECTION_RELOAD_STEP_TIME = 5000;
    const CONNECTION_RELOA_MAX_STEP_TIME = 30000;

    private $reloadTime = [];

    /**
     * @var LoadBalancingStrategyInterface
     */
    private $loadBalancingStrategy;

    public function __construct(array $config, $strategy)
    {
        $this->init($config, $strategy);
    }

    private function init($config, $strategy)
    {
        $this->config = $config;
        $this->createConnections();
        $this->initLoadBalancingStrategy($strategy);
    }

    private function createConnections()
    {
        foreach ($this->config as $config) {
            $this->createConnection($config);
        }
    }

    public function createConnection($config)
    {
        $novaClientFactory = new NovaClientFactory($config);
        $connection = $novaClientFactory->create();
        if ($connection instanceof Connection) {
            $key = spl_object_hash($connection);
            $this->waitingConnections[$key] = $connection;
            $connection->setPool($this);
        }
    }

    public function connecting(Connection $connection)
    {
        $key = spl_object_hash($connection);
        $this->connections[$key] = $connection;
        unset($this->waitingConnections[$key]);
    }

    private function initLoadBalancingStrategy($strategy)
    {
        if (!isset($this->loadBalancingStrategyMap[$strategy])) {
            throw new CanNotFindLoadBalancingStrategeMapException();
        }
        $loadBalancingStrategy = $this->loadBalancingStrategyMap[$strategy];
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

    public function get()
    {
        yield $this->loadBalancingStrategy->get();
    }

    public function reload(array $config)
    {
        $interval = $this->getReloadTime($config['host'], $config['port']);
        if ($interval === 0) {
            $this->incReloadTime($config['host'], $config['port']);
            $this->createConnection($config);
            return;
        }
        Timer::after($interval, function () use ($config) {
            $this->createConnection($config);
        });
    }

    public function remove(Connection $conn)
    {
        $key = spl_object_hash($conn);
        if (!isset($this->connections[$key])) {
            return false;
        }
        unset($this->connections[$key]);
    }

    public function removeConfig($config)
    {
        foreach ($this->config as $key => $tmpConfig) {
            if ($tmpConfig['host'] == $config['host'] && $tmpConfig['port'] == $config['port']) {
                unset($this->config[$key]);
            }
        }
    }

    public function addConfig($config)
    {
        $this->config[] = $config;
    }

    public function recycle(Connection $conn)
    {

    }

    private function getReloadTime($host, $port)
    {
        $key = $this->getReloadTimeKey($host, $port);
        if (!isset($this->reloadTime[$key])) {
            $this->reloadTime[$key] = 0;
        }
        return $this->reloadTime[$key];
    }

    private function incReloadTime($host, $port)
    {
        $key = $this->getReloadTimeKey($host, $port);
        $this->reloadTime[$key] += self::CONNECTION_RELOAD_STEP_TIME;
        $this->reloadTime[$key] = $this->reloadTime[$key] >= self::CONNECTION_RELOA_MAX_STEP_TIME ? self::CONNECTION_RELOA_MAX_STEP_TIME : $this->reloadTime[$key];
    }

    public function resetReloadTime($config)
    {
        $this->reloadTime[$this->getReloadTimeKey($config['host'], $config['port'])] = 0;
    }

    private function getReloadTimeKey($host, $port)
    {
        return $host . ':' . $port;
    }

    public function getReloadJobId($host, $port)
    {
        return spl_object_hash($this).$this->getReloadTimeKey($host, $port);
    }
}