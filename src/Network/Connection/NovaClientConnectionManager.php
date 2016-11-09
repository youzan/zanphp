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
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaServiceNameMethodException;
use Zan\Framework\Network\Connection\Exception\CanNotParseServiceNameException;
use Zan\Framework\Network\Connection\Exception\CanNotFindNovaClientPoolNameByAppNameException;
use Zan\Framework\Foundation\Core\RunMode;

class NovaClientConnectionManager
{
    use Singleton;

    private $novaClientPools = [];

    private $novaServiceNameToMethodsMap = [];
    
    private $appNameToNovaClientPoolNameMap = [];

    private $appNameToServerMap = [];

    public function work($appName, $servers, $novaClientPoolName = '')
    {
        $novaConfig = Config::get('connection.nova');
        $config = [];

        foreach ($servers as $server) {
            $services = $server['services'];
            if (is_array($services) && [] !== $services) {
                $novaClientPoolName = $this->formatNovaServiceNameToMethodsMap($services);
                $this->addAppNameToServerMap($appName, $server);
            }
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $config[$novaConfig['host'].':'.$novaConfig['port']] = $novaConfig;
        }

        $this->appNameToNovaClientPoolNameMap[$appName] = $novaClientPoolName;
        $this->novaClientPools[$novaClientPoolName] = new NovaClientPool($appName, $config, $novaConfig['load_balancing_strategy']);
    }

    private function formatNovaServiceNameToMethodsMap($services)
    {
        $novaClientPoolName = '';
        foreach ($services as $service) {
            $novaClientPoolName = '' == $novaClientPoolName ? $this->parseNovaClientPoolName($service['service']) : $novaClientPoolName;
            if (isset($this->novaServiceNameToMethodsMap[$service['service']])) {
                continue;
            }
            $this->novaServiceNameToMethodsMap[$service['service']] = ['nova_client_pool_name' => $novaClientPoolName, 'methods' => $service['methods']];
        }
        return $novaClientPoolName;
    }

    private function parseNovaClientPoolName($novaServiceName)
    {
        $exp = explode('.', $novaServiceName);
        if (isset($exp[0]) && isset($exp[1]) && isset($exp[2])) {
            return $exp[0] . '.' . $exp[1] . '.' . $exp[2];
        }
        throw new CanNotParseServiceNameException('nova service name :'.$novaServiceName);
    }

    public function get($novaServiceName, $method)
    {
        $pool = $this->getPool($novaServiceName, $method);
        yield $pool->get();
    }

    private function getPool($novaServiceName, $method)
    {
        if (!in_array(RunMode::get(), ['test', 'qatest'])) {
            if (!isset($this->novaServiceNameToMethodsMap[$novaServiceName])) {
                throw new CanNotFindNovaServiceNameException('nova service name :'.$novaServiceName);
            }
            if (!in_array($method, $this->novaServiceNameToMethodsMap[$novaServiceName]['methods'])) {
                throw new CanNotFindNovaServiceNameMethodException('nova service name :'.$novaServiceName.'&method :'.$method);
            }
        }

        $novaClientPoolName = $this->parseNovaClientPoolName($novaServiceName);

        if (!isset($this->novaClientPools[$novaClientPoolName])) {
            throw new CanNotFindNovaClientPoolException('nova client pool name :'.$novaClientPoolName);
        }

        return $this->novaClientPools[$novaClientPoolName];
    }

    private function getPoolByAppName($appName)
    {
        if (!isset($this->appNameToNovaClientPoolNameMap[$appName])) {
            throw new CanNotFindNovaClientPoolNameByAppNameException('app name :'.$appName);
        }
        $novaClientPoolName = $this->appNameToNovaClientPoolNameMap[$appName];

        if (!isset($this->novaClientPools[$novaClientPoolName])) {
            throw new CanNotFindNovaClientPoolException('nova client pool name :'.$novaClientPoolName);
        }

        return $this->novaClientPools[$novaClientPoolName];
    }

    private function addAppNameToServerMap($appName, $server)
    {
        $this->appNameToServerMap[$appName][$server['host'].':'.$server['port']] = $server;
    }

    private function removeSeverFromAppNameToServerMap($appName, $server)
    {
        if (isset($this->appNameToServerMap[$appName][$server['host'].':'.$server['port']])) {
            unset($this->appNameToServerMap[$appName][$server['host'].':'.$server['port']]);
        }
        return true;
    }

    private function updateAppNameToServerMap($appName, $server)
    {
        if (!isset($this->appNameToServerMap[$appName][$server['host'].':'.$server['port']])) {
            return;
        }
        $this->appNameToServerMap[$appName][$server['host'].':'.$server['port']] = $server;
    }

    public function getServersFromAppNameToServerMap($appName)
    {
        return isset($this->appNameToServerMap[$appName]) ? $this->appNameToServerMap[$appName] : [];
    }

    public function addOnline($appName, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        $pool = $this->getPoolByAppName($appName);
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->formatNovaServiceNameToMethodsMap($server['services']);
            $this->addAppNameToServerMap($appName, $server);

            $pool->createConnection($novaConfig);
            $pool->addConfig($novaConfig);
        }
    }

    public function offline($appName, $servers)
    {
        $pool = $this->getPoolByAppName($appName);
        foreach ($servers as $server) {
            $this->removeSeverFromAppNameToServerMap($appName, $server);
            $connection = $pool->getConnectionByHostPort($server['host'], $server['port']);
            if (null !== $connection && $connection instanceof Connection) {
                $pool->remove($connection);
                $pool->removeConfig($server);
            }
        }
    }

    public function update($appName, $servers)
    {
        $novaConfig = Config::get('connection.nova');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $this->updateAppNameToServerMap($appName, $server);
            $this->formatNovaServiceNameToMethodsMap($server['services']);
        }
    }
}