<?php
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Connection\Exception\CanNotFindLoadBalancingStrategeMapException;
use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\RunMode;
use ZanPHP\Contracts\LoadBalance\LoadBalancer;
use ZanPHP\Contracts\LoadBalance\Node;
use ZanPHP\Contracts\ServiceChain\ServiceChainer;
use ZanPHP\LoadBalance\LVSRoundRobinLoadBalance;
use ZanPHP\LoadBalance\RandomLoadBalance;

class NovaClientPool
{
    /**
     * @var Connection[]
     */
    private $connections = [];

    private $waitingConnections = [];

    private $config;

    private $appName;

    private $loadBalancingStrategyMap = [
        "polling"       => LVSRoundRobinLoadBalance::class, // 兼容旧配置
        "random"        => RandomLoadBalance::class,
        "roundRobin"    => LVSRoundRobinLoadBalance::class,
        /*
        "leastActive"   => LeastActiveLoadBalance::class,
        "consistentHash"=> ConsistentHashLoadBalance::class,
        */
    ];

    const CONNECTION_RELOAD_STEP_TIME = 5000;
    const CONNECTION_RELOA_MAX_STEP_TIME = 30000;

    private $reloadTime = [];

    /**
     * @var LoadBalancer
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
        $this->loadBalancingStrategy = new $loadBalancingStrategy;
    }

    public function updateLoadBalancingStrategy($pool)
    {
        // TODO loadBalancer 接口是否添加一个onRefresh接口用来处理有状态的负载均衡实现, 感知外部结点变化
        // $this->loadBalancingStrategy->initServers($pool);
        /** @var Node $node */
        // foreach ($this->connections as $node) { $node->reset()?! }
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
        $connections = $this->connections;

        try {
            $serviceChain = (yield getContext("service-chain"));
            if ($serviceChain instanceof ServiceChainer) {
                $value = (yield getContext("service-chain-value"));
                if ($value === null) {
                    $connections = (yield $this->getConnectionsWithoutServiceChain($serviceChain));
                } else {
                    $connections = (yield $this->getConnectionsWithServiceChain($serviceChain, $value));
                }
            }
        } catch (\Throwable $t) {
            sys_error("get connection by servcie chain error: " . $t->getMessage());
        } catch (\Exception $e) {
            sys_error( "get connection by servcie chain error: " . $e->getMessage());
        }

        yield $this->loadBalancingStrategy->select($connections);
    }

    /**
     * 当前调用包含Service Chain标识，则路由到归属于该Service Chain的任意服务节点，
     * 如果没有归属于该Service Chain的服务节点，则排除掉所有隶属于Service Chain的服务节点之后路由到任意服务节点
     * @param ServiceChainer $serviceChainer
     * @param $key
     * @return \Generator|void
     */
    private function getConnectionsWithServiceChain(ServiceChainer $serviceChainer, $key)
    {
        $endpoints = (yield $serviceChainer->getEndpoints($this->appName, $key));

        if ($endpoints) {
            $connections = [];
            foreach ($this->connections as $connection) {
                $config = $connection->getConfig();
                $host = $config["host"];
                $port = $config["port"];
                if (isset($endpoints["$host:$port"])) {
                    $connections[] = $connection;
                }
            }

            if ($connections) {
                yield $connections;
                return;
            }
        }

        yield $this->getConnectionsWithoutServiceChain($serviceChainer);
    }

    /**
     * 获取所有没有service chain标记的连接
     * @param ServiceChainer $serviceChainer
     * @return \Generator
     */
    private function getConnectionsWithoutServiceChain(ServiceChainer $serviceChainer)
    {
        $allEndpoints = (yield $serviceChainer->getEndpoints($this->appName));

        if (empty($allEndpoints)) {
            yield $this->connections;
        } else {
            $connections = [];
            $endpoints = array_merge(...array_values($allEndpoints));
            foreach ($this->connections as $connection) {
                $config = $connection->getConfig();
                $host = $config["host"];
                $port = $config["port"];
                if (!isset($endpoints["$host:$port"])) {
                    $connections[] = $connection;
                }
            }
            yield $connections;
        }
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