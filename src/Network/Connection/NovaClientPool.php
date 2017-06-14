<?php
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Connection\Exception\CanNotFindLoadBalancingStrategeMapException;
use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\Connection\LoadBalancingStrategy\Polling;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\RunMode;

class NovaClientPool
{
    private $connections = [];

    private $waitingConnections = [];

    private $config;

    private $appName;

    private $loadBalancingStrategyMap = [
        Polling::NAME => Polling::class,
    ];

    const CONNECTION_RELOAD_STEP_TIME = 5000;
    const CONNECTION_RELOA_MAX_STEP_TIME = 30000;

    private $reloadTime = [];

    /**
     * @var LoadBalancingStrategyInterface
     */
    private $loadBalancingStrategy;

    public function __construct($appName, array $config, $loadBalancingStrategy)
    {
        $this->init($appName, $config, $loadBalancingStrategy);
    }

    private function init($appName, $config, $loadBalancingStrategy)
    {
        $this->config = $config;
        $this->appName = $appName;
        $this->createConnections();
        $this->initLoadBalancingStrategy($loadBalancingStrategy);
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
        $this->loadBalancingStrategy = Di::make($loadBalancingStrategy, [$this]);
    }

    public function updateLoadBalancingStrategy($pool)
    {
        $this->loadBalancingStrategy->initServers($pool);
    }

    public function getConnections()
    {
        return $this->connections;
    }

    public function getConfig()
    {
        return $this->config;
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
        $canReload = $this->checkCanReload($config);
        if (false === $canReload) {
            return;
        }
        $interval = $this->getReloadTime($config['host'], $config['port']);
        $this->incReloadTime($config['host'], $config['port']);
        if ($interval === 0) {
            $this->createConnection($config);
            return;
        }
        Timer::after($interval, function () use ($config) {
            $this->createConnection($config);
        });
    }

    private function checkCanReload($config)
    {
        if ('test' == RunMode::get()) {
            return true;
        }

        $services = ServerStore::getInstance()->getServices($this->appName);
        if (null == $services || [] == $services) {
            return false;
        }
        foreach ($services as $service) {
            // 其他环境 服务未下线 持续重连 服务已下线 停止重连
            if ($service['host'] == $config['host'] && $service['port'] == $config['port']) {
                return true;
            }
        }
        return false;
    }

    public function remove(Connection $conn)
    {
        $key = spl_object_hash($conn);

        if (isset($this->waitingConnections[$key])) {
            unset($this->waitingConnections[$key]);
        }

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
        $this->config[$config['host'].':'.$config['port']] = $config;
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