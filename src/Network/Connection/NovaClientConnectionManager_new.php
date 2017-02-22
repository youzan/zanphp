<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\LoadBalancingStrategy\Polling;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaServiceNameMethodException;
use Zan\Framework\Network\Connection\Exception\CanNotParseServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolNameByAppNameException;
use Zan\Framework\Foundation\Core\RunMode;

class NovaClientConnectionManager
{
    use Singleton;

    /**
     * serviceKey => server info
     * @var array
     */
    private $serviceMap = [];

    /**
     * appName => NovaClientPool
     * @var NovaClientPool[]
     */
    private $poolMap = [];

    private $novaConfig;

    public function __construct()
    {
        $this->novaConfig = Config::get("connection.nova", []);
        isset($this->novaConfig["load_balancing_strategy"]) ?
            $this->novaConfig["load_balancing_strategy"] : Polling::NAME;
    }

    private function serviceKey($protocol, $namespace, $service)
    {
        return "$protocol:$namespace:$service";
    }

    private function makeNovaConfig($server)
    {
        $key = "{$server["host"]}:{$server["port"]}";
        $value = [
            "host" => $server["host"],
            "port" => $server["port"],
            "weight" => isset($server["weight"]) ? $server["weight"] : 100
        ] + $this->novaConfig;

        return [$key, $value];
    }

    public function get($protocol, $namespace, $service, $method)
    {
        $serviceKey = $this->serviceKey($protocol, $namespace, $service);
        if (!isset($this->serviceMap[$serviceKey])) {
            throw new CanNotFindNovaClientPoolException("service=$service");
        }

        $service = $this->serviceMap[$serviceKey];
        if (!in_array($method, $service["methods"], true)) {
            throw new CanNotFindNovaServiceNameMethodException("service=$service, $method=$method");
        }

        $pool = $this->getPool($service["app_name"]);
        yield $pool->get();
    }

    private function getPool($appName)
    {
        if (!isset($this->poolMap[$appName])) {
            throw new CanNotFindNovaClientPoolException("app_name=$appName");
        }

        return $this->poolMap[$appName];
    }

    private function parserServers(array $servers)
    {
        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $namespace = $server["namespace"];

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $namespace, $service["service"]);
                $this->serviceMap[$serviceKey] = $service + $server;
            }

            list($k, $v) = $this->makeNovaConfig($server);
            yield $k => $v;
        }
    }

    public function work($appName, array $servers)
    {
        $config = [];
        foreach ($this->parserServers($servers) as $key => $novaConfig) {
            $config[$key] = $novaConfig;
        }
        $pool = new NovaClientPool($appName, $config, $this->novaConfig["load_balancing_strategy"]);;
        $this->poolMap[$appName] = $pool;
    }

    public function addOnline($appName, array $servers)
    {
        $pool = $this->getPool($appName);
        foreach ($this->parserServers($servers) as $key => $novaConfig) {
            $pool->createConnection($novaConfig);
            $pool->addConfig($novaConfig);
            $pool->updateLoadBalancingStrategy($pool);
        }
    }

    public function update($appName, array $servers)
    {
        $pool = $this->getPool($appName);
        foreach ($this->parserServers($servers) as $novaConfig) {
            $pool->addConfig($novaConfig);
            $pool->updateLoadBalancingStrategy($pool);
        }
    }

    public function offline($appName, array $servers)
    {
        $pool = $this->getPool($appName);

        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $namespace = $server["namespace"];

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $namespace, $service["service"]);

                if (isset($this->serviceMap[$serviceKey])) {

                    $connection = $pool->getConnectionByHostPort($server["host"], $server["port"]);
                    if (null !== $connection && $connection instanceof Connection) {
                        $pool->remove($connection);
                        $pool->removeConfig($server);
                        $pool->updateLoadBalancingStrategy($pool);
                    }

                    unset($this->serviceMap[$serviceKey]);
                }
            }
        }
    }

    public function getServersFromAppNameToServerMap($appName)
    {
        return isset($this->appNameToServerMap[$appName]) ? $this->appNameToServerMap[$appName] : [];
    }
}