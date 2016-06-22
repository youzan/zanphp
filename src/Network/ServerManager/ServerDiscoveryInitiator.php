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
        if (!isset($config['no_need_discovery']) || !is_array($config['no_need_discovery']) || [] === $config['no_need_discovery']) {
            return;
        }
        if (isset($config['app_names']) && is_array($config['app_names']) && [] !== $config['app_names']) {
            foreach ($config['app_names'] as $key => $appName) {
                if (in_array($appName, $config['no_need_discovery'])) {
                    unset($config['app_names'][$key]);
                }
            }
        }
        foreach ($config['no_need_discovery'] as $appName) {
            if (!isset($config['novaApi'][$appName])) {
                continue;
            }
            if (!isset($config['connection'][$appName])) {
                continue;
            }

            $config['path'] = Path::getRootPath() . $config['novaApi'][$appName]['path'];
            Nova::init($config);

            $services = Nova::getAvailableService();
            $servers[$config['connection'][$appName]['host'].':'.$config['connection'][$appName]['port']] = [
                'app_name' => $appName,
                'host' => $config['connection'][$appName]['host'],
                'port' => $config['connection'][$appName]['port'],
                'services' => $services,
            ];
            NovaClientConnectionManager::getInstance()->work($appName, $servers);
        }

        return $config;
    }
}