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
use Zan\Framework\Network\Connection\Exception\CanNotFindServiceNamePoolException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientModuleByServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindModuleMethodByServiceNameException;

class NovaClientConnectionManager
{
    use Singleton;

    private $novaClientPool = [];

    private $serviceToModuleMap = [];

    private $serverConfig = [];

    public function work($module, $servers)
    {
        $loadBalancing = Config::get('loadBalancing');
        $novaConfig = Config::get('connection.nova');
        $config = [];
        foreach ($servers as $server) {
            $this->serverConfig[$module][$server['host'].':'.$server['port']] = $server;
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->addServiceToModuleMap($module, $server['services']);
            $config[$novaConfig['port'].':'.$novaConfig['port']] = $novaConfig;
        }
        $this->novaClientPool[$module] = new NovaClientPool($config, $loadBalancing['strategy'], $module);
    }

    private function addServiceToModuleMap($module, $services)
    {
        foreach ($services as $service) {
            $this->serviceToModuleMap[$service['service']] = ['module' => $module, 'methods' => $service['methods']];
        }
    }

    public function getSeverConfig($module)
    {
        return isset($this->serverConfig[$module]) ? $this->serverConfig[$module] : [];
    }

    /**
     * @param $module
     * @return NovaClientPool | null
     */
    public function getPool($module)
    {
        if (!isset($this->novaClientPool[$module])) {
            throw new CanNotFindServiceNamePoolException();
        }
        return $this->novaClientPool[$module];
    }

    public function get($serviceName, $method)
    {
        $module = $this->getModule($serviceName, $method);
        $pool = $this->getPool($module);
        yield $pool->get();
    }

    public function offline($module, $servers)
    {
        $pool = $this->getPool($module);
        foreach ($servers as $server) {
            $connection = $pool->getConnectionByHostPort($server['host'], $server['port']);
            if (null !== $connection && $connection instanceof Connection) {
                $pool->remove($connection);
                $pool->removeConfig($server);
            }
        }
    }

    public function addOnline($module, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        $pool = $this->getPool($module);
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->addServiceToModuleMap($module, $server['services']);
            $pool->createConnection($novaConfig);
            $pool->addConfig($novaConfig);
        }
    }

    private function getModule($serviceName, $method)
    {
        if (!isset($this->serviceToModuleMap[$serviceName])) {
            throw new CanNotFindNovaClientModuleByServiceNameException();
        }
        if (!in_array($method, $this->serviceToModuleMap[$serviceName]['methods'])) {
            throw new CanNotFindModuleMethodByServiceNameException();
        }
        return $this->serviceToModuleMap[$serviceName]['module'];
    }

    public function update($module, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            foreach ($server['services'] as $service) {
                $this->serviceToModuleMap[$service['service']] = ['module' => $module, 'methods' => $service['methods']];
            }
        }
    }
}