<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolByAppNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientAppNameByServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindAppNameByMethodException;

class NovaClientConnectionManager
{
    use Singleton;

    private $novaClientPool = [];

    private $serviceToAppNameMap = [];

    private $serverConfig = [];

    public function work($appName, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        $config = [];
        foreach ($servers as $server) {
            $this->addServerConfig($appName, $server);

            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->addServiceToAppNameMap($appName, $server['services']);
            $config[$novaConfig['host'].':'.$novaConfig['port']] = $novaConfig;
        }
        $this->novaClientPool[$appName] = new NovaClientPool($appName, $config, $novaConfig['load_balancing_strategy']);
    }

    private function addServiceToAppNameMap($appName, $services)
    {
        foreach ($services as $service) {
            $this->serviceToAppNameMap[$service['service']] = ['app_name' => $appName, 'methods' => $service['methods']];
        }
    }

    private function addServerConfig($appName, $servers)
    {
        foreach ($servers as $server) {
            $this->serverConfig[$appName][$server['host'].':'.$server['port']] = $server;
        }
    }

    private function deleteServerConfig($appName, $server)
    {
        if (isset($this->serverConfig[$appName][$server['host'].':'.$server['port']])) {
            unset($this->serverConfig[$appName][$server['host'].':'.$server['port']]);
        }
        return true;
    }

    public function getSeverConfig($appName)
    {
        return isset($this->serverConfig[$appName]) ? $this->serverConfig[$appName] : [];
    }

    /**
     * @param $appName
     * @return NovaClientPool | null
     */
    public function getPool($appName)
    {
        if (!isset($this->novaClientPool[$appName])) {
            throw new CanNotFindNovaClientPoolByAppNameException();
        }
        return $this->novaClientPool[$appName];
    }

    public function get($serviceName, $method)
    {
        $appName = $this->getAppName($serviceName, $method);
        $pool = $this->getPool($appName);
        yield $pool->get();
    }

    public function offline($appName, $servers)
    {
        $pool = $this->getPool($appName);
        foreach ($servers as $server) {
            $this->deleteServerConfig($appName, $server);
            $connection = $pool->getConnectionByHostPort($server['host'], $server['port']);
            if (null !== $connection && $connection instanceof Connection) {
                $pool->remove($connection);
                $pool->removeConfig($server);
            }
        }
    }

    public function addOnline($appName, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        $pool = $this->getPool($appName);
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->addServiceToAppNameMap($appName, $server['services']);
            $this->addServerConfig($appName, $server);

            $pool->createConnection($novaConfig);
            $pool->addConfig($novaConfig);
        }
    }

    private function getAppName($serviceName, $method)
    {
        if (!isset($this->serviceToAppNameMap[$serviceName])) {
            throw new CanNotFindNovaClientAppNameByServiceNameException();
        }
        if (!in_array($method, $this->serviceToAppNameMap[$serviceName]['methods'])) {
            throw new CanNotFindAppNameByMethodException();
        }
        return $this->serviceToAppNameMap[$serviceName]['app_name'];
    }

    public function update($appName, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            foreach ($server['services'] as $service) {
                $this->serviceToAppNameMap[$service['service']] = ['app_name' => $appName, 'methods' => $service['methods']];
            }
        }
    }
}