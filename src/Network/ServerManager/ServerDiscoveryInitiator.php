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

    public function init($workerId)
    {
        $config = Config::get('haunt');
        if (empty($config)) {
            throw new ServerConfigException();
        }
        $config = $this->noNeedDiscovery($config);
        if (!isset($config['app_names']) || [] === $config['app_names']) {
            return;
        }

        // 为特定app指定protocol 与 domain
        $appConfigs = Config::get('haunt.app_configs', []);
        foreach ($config['app_names'] as $appName) {
            if (!isset($appConfigs[$appName])) {
                $appConfigs[$appName] = [
                    "protocol" => ServerDiscovery::DEFAULT_PROTOCOL,
                    "namespace" => ServerDiscovery::DEFAULT_NAMESPACE
                ];
            }
        }

        // 如果加锁的worker挂了, 锁在生命周期就永远不会被释放
        // 这里记录加锁的workerId, 在worker error回调检查异常退出的worker是否是持有锁的worker
        if (ServerStore::getInstance()->lockDiscovery($workerId)) {
            sys_error("worker #$workerId service discovery from etcd");
            foreach ($config['app_names'] as $appName) {
                $appConf = $appConfigs[$appName];
                $serverDiscovery = new ServerDiscovery($config, $appName, $appConf["protocol"], $appConf["namespace"]);
                $serverDiscovery->workByEtcd();
            }
        } else {
            sys_error("worker #$workerId service discovery from apcu");
            foreach ($config['app_names'] as $appName) {
                $appConf = $appConfigs[$appName];
                $serverDiscovery = new ServerDiscovery($config, $appName, $appConf["protocol"], $appConf["namespace"]);
                $serverDiscovery->workByStore();
            }
        }
    }

    public function unlockDiscovery($workerId)
    {
        return ServerStore::getInstance()->unlockDiscovery($workerId);
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
            //reset $servers
            $servers = [];
            $servers[$noNeedDiscovery['connection'][$appName]['host'].':'.$noNeedDiscovery['connection'][$appName]['port']] = [
                'app_name' => $appName,
                'host' => $noNeedDiscovery['connection'][$appName]['host'],
                'port' => $noNeedDiscovery['connection'][$appName]['port'],
                'services' => [],
            ];
            NovaClientConnectionManager::getInstance()->work($appName, $servers, substr(str_replace('\\', '.', strtolower($novaConfig['namespace'])), 0, -1));
        }

        return $config;
    }
}