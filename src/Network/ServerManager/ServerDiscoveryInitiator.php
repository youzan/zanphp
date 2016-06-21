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
    }
}