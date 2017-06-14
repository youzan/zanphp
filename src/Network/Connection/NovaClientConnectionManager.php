<?php
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\LoadBalancingStrategy\Polling;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaServiceNameMethodException;

class NovaClientConnectionManager
{
    use Singleton;

    /**
     * serviceKey => server info
     * @var array
     */
    private $serviceMap;

    /**
     * appName => NovaClientPool
     * @var NovaClientPool[]
     */
    private $poolMap;

    private $novaConfig;

    public function __construct()
    {
        $this->serviceMap = [];
        $this->poolMap = [];
        $this->novaConfig = Config::get("connection.nova", []);
        if (!isset($this->novaConfig["load_balancing_strategy"])) {
            $this->novaConfig["load_balancing_strategy"] = Polling::NAME;
        }
    }

    public function get($protocol, $domain, $service, $method, $retry = 5)
    {
        $serviceKey = $this->serviceKey($protocol, $domain, $service);

        if (isset($this->serviceMap[$serviceKey])) {
            $serviceMap = $this->serviceMap[$serviceKey];
            if (in_array($method, $serviceMap["methods"], true)) {
                $pool = $this->getPool($serviceMap["app_name"]);
                yield $pool->get();
            } else {
                throw new CanNotFindNovaServiceNameMethodException("service=$service, method=$method");
            }
        } else {
            if ($retry > 0) {
                yield taskSleep(200);
                yield $this->get($protocol, $domain, $service, $method, --$retry);
            } else {
                throw new CanNotFindNovaClientPoolException("proto=$protocol, domain=$domain, service=$service, method=$method");
            }
        }
    }

    private function getPool($appName, array $servers = [])
    {
        if (!isset($this->poolMap[$appName]) && $servers) {
            $this->work($appName, $servers);
        }

        if (isset($this->poolMap[$appName])) {
            return $this->poolMap[$appName];
        } else {
            throw new CanNotFindNovaClientPoolException("app_name=$appName");
        }
    }

    public function work($appName, array $servers)
    {
        $config = [];
        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $domain = $server["namespace"];

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $domain, $service["service"]);
                $this->serviceMap[$serviceKey] = $service + $server;
            }

            list($key, $novaConfig) = $this->makeNovaConfig($server);
            $config[$key] = $novaConfig;
        }

        $pool = new NovaClientPool($appName, $config, $this->novaConfig["load_balancing_strategy"]);;
        $this->poolMap[$appName] = $pool;
    }

    public function addOnline($appName, array $servers)
    {
        $pool = $this->getPool($appName, $servers);

        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $domain = $server["namespace"];

            sys_echo("nova client online " . $this->serverInfo($server));

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $domain, $service["service"]);
                $this->serviceMap[$serviceKey] = $service + $server;
            }

            list(, $novaConfig) = $this->makeNovaConfig($server);
            $pool->createConnection($novaConfig);
            $pool->addConfig($novaConfig);
            $pool->updateLoadBalancingStrategy($pool);
        }
    }

    public function update($appName, array $servers)
    {
        $pool = $this->getPool($appName, $servers);

        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $domain = $server["namespace"];

            sys_echo("nova client update " . $this->serverInfo($server));

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $domain, $service["service"]);
                $this->serviceMap[$serviceKey] = $service + $server;
            }

            list(, $novaConfig) = $this->makeNovaConfig($server);
            $pool->addConfig($novaConfig);
            $pool->updateLoadBalancingStrategy($pool);
        }
    }

    public function offline($appName, array $servers)
    {
        $pool = $this->getPool($appName, $servers);

        foreach ($servers as $server) {
            $protocol = $server["protocol"];
            $domain = $server["namespace"];

            sys_echo("nova client offline " . $this->serverInfo($server));

            foreach ($server["services"] as $service) {
                $serviceKey = $this->serviceKey($protocol, $domain, $service["service"]);

                if (isset($this->serviceMap[$serviceKey])) {

                    $connection = $pool->getConnectionByHostPort($server["host"], $server["port"]);
                    if (null !== $connection && $connection instanceof Connection) {
                        $pool->remove($connection);
                    }

                    $pool->removeConfig($server);
                    $pool->updateLoadBalancingStrategy($pool);

                    if (empty($pool->getConfig())) {
                        unset($this->serviceMap[$serviceKey]);
                    }
                }
            }
        }
    }

    public function getServersFromAppNameToServerMap($appName)
    {
        $map = [];
        foreach ($this->serviceMap as $key => $server) {
            if ($appName === $server["app_name"]) {
                $pool = $this->getPool($appName);
                $config = $pool->getConfig();
                // 同一个service 可能有多个节点
                foreach ($config as $hostPort => $item) {
                    $map[$hostPort] = $item["server"];
                }
            }
        }
        return $map;
    }

    private function serviceKey($protocol, $domain, $service)
    {
        // return "$protocol:$domain:$service";
        // 无法获取客户端调用domain信息, 忽略
        return "$protocol::$service";
    }

    private function makeNovaConfig($server)
    {
        $key = "{$server["host"]}:{$server["port"]}";
        $value = [
                "host" => $server["host"],
                "port" => $server["port"],
                "weight" => isset($server["weight"]) ? $server["weight"] : 100,
                "server" => $server, // extra info for debug
            ] + $this->novaConfig;

        return [$key, $value];
    }

    private function serverInfo($server)
    {
        $info = [];
        foreach ($server as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $info[] = "$k=$v";
        }
        return '[' . implode(", ", $info) . ']';
    }
}