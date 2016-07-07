<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/31
 * Time: 下午5:17
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Network\ServerManager\ServerDiscovery;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\ServerManager\ServerStore;

use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Core\Path;

class ServerDiscoveryInitiator
{
    use Singleton;

    private $lockDiscovery = 0;

    public function init()
    {
        $config = Config::get('haunt');
        if (empty($config)) {
            throw new ServerConfigException();
        }
        $config = $this->noNeedDiscovery($config);
        if (!isset($config['app_names']) || [] === $config['app_names']) {
            return;
        }
        if (ServerStore::getInstance()->lockDiscovery()) {
            $this->lockDiscovery = 1;
            foreach ($config['app_names'] as $appName) {
                $serverDiscovery = new ServerDiscovery($config, $appName);
                $serverDiscovery->workByEtcd();
            }
        } else {
            foreach ($config['app_names'] as $appName) {
                $serverDiscovery = new ServerDiscovery($config, $appName);
                $serverDiscovery->workByStore();
            }
        }
    }

    public function resetLockDiscovery()
    {
        if (1 == $this->lockDiscovery) {
            return ServerStore::getInstance()->resetLockDiscovery();
        }
        return true;
    }

    public function noNeedDiscovery($config)
    {
        $noNeedDiscovery = Config::get('service_discovery');
        if (empty($noNeedDiscovery)) {
            return $config;
        }
        if (!isset($noNeedDiscovery['app_names']) || !is_array($noNeedDiscovery['app_names']) || [] === $noNeedDiscovery['app_names']) {
            return $config;
        }
        if (isset($config['app_names']) && is_array($config['app_names']) && [] !== $config['app_names']) {
            foreach ($config['app_names'] as $key => $appName) {
                if (in_array($appName, $noNeedDiscovery['app_names'])) {
                    unset($config['app_names'][$key]);
                }
            }
        }
        foreach ($noNeedDiscovery['app_names'] as $appName) {
            if (!isset($noNeedDiscovery['novaApi'][$appName])) {
                continue;
            }
            if (!isset($noNeedDiscovery['connection'][$appName])) {
                continue;
            }
            $novaConfig = $noNeedDiscovery['novaApi'][$appName];
            $servers = [];
            $servers[$noNeedDiscovery['connection'][$appName]['host'].':'.$noNeedDiscovery['connection'][$appName]['port']] = [
                'app_name' => $appName,
                'host' => $noNeedDiscovery['connection'][$appName]['host'],
                'port' => $noNeedDiscovery['connection'][$appName]['port'],
                'services' => [],
            ];
            NovaClientConnectionManager::getInstance()->work($appName, $servers, str_replace('\\', '.', strtolower($novaConfig['namespace'])));
        }

        return $config;
    }
}