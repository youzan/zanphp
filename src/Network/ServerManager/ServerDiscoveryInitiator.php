<?php
namespace Zan\Framework\Network\ServerManager;

use Kdt\Iron\Nova\Foundation\TService;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;

class ServerDiscoveryInitiator
{
    use Singleton;

    public function init($workerId)
    {
        $config = Config::get('registry');
        if (empty($config)) {
            throw new ServerConfigException("registry config is not found");
        }
        $config = $this->noNeedDiscovery($config);
        if (!isset($config['app_names']) || [] === $config['app_names']) {
            return;
        }

        // 为特定app指定protocol 与 domain
        $appConfigs = Config::get('registry.app_configs', []);
        foreach ($config['app_names'] as $appName) {
            if (!isset($appConfigs[$appName]) || !is_array($appConfigs[$appName])) {
                $appConfigs[$appName] = [];
            }
            $appConfigs[$appName] += [
                "protocol" => ServerDiscovery::DEFAULT_PROTOCOL,
                "namespace" => ServerDiscovery::DEFAULT_NAMESPACE
            ];
        }

        if ($workerId === 0) {
        // if (ServerStore::getInstance()->lockDiscovery($workerId)) {
            sys_echo("worker *$workerId service discovery from etcd");
            foreach ($config['app_names'] as $appName) {
                $appConf = $appConfigs[$appName];
                $serverDiscovery = new ServerDiscovery($config, $appName, $appConf["protocol"], $appConf["namespace"]);
                $serverDiscovery->workByEtcd();
            }
        } else {
            sys_echo("worker *$workerId service discovery from apcu");
            foreach ($config['app_names'] as $appName) {
                $appConf = $appConfigs[$appName];
                $serverDiscovery = new ServerDiscovery($config, $appName, $appConf["protocol"], $appConf["namespace"]);
                $serverDiscovery->workByStore();
            }
        }
    }

    public function unlockDiscovery($workerId)
    {
        // return ServerStore::getInstance()->unlockDiscovery($workerId);
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
            $novaConfig += [ "domain" => ServerDiscovery::DEFAULT_NAMESPACE ];


            $services = [];
            $path = Path::getRootPath() . $novaConfig["path"] . "/";
            $baseNamespace = $novaConfig["namespace"];
            $specMap = Nova::getSpec($path, $baseNamespace);
            $ts = \nova_get_time();
            foreach ($specMap as $className => $spec) {
                $services[] = [
                    "language"=> "php",
                    "version"=> "1.0.0",
                    "timestamp"=> $ts,
                    "service" => TService::getNovaServiceName($spec->getServiceName()),
                    "methods"=> $spec->getServiceMethods(),
                ];
            }

            //reset $servers
            $servers = [];
            $servers[$noNeedDiscovery['connection'][$appName]['host'].':'.$noNeedDiscovery['connection'][$appName]['port']] = [
                'app_name' => $appName,
                'host' => $noNeedDiscovery['connection'][$appName]['host'],
                'port' => $noNeedDiscovery['connection'][$appName]['port'],
                'services' => $services,
                'namespace' => $novaConfig['domain'],
                'protocol' => "nova",
                'status' => 1,
                'weight' => 100,
            ];

            ServerStore::getInstance()->setServices($appName, $servers);
            /* @var $connMgr NovaClientConnectionManager */
            $connMgr = NovaClientConnectionManager::getInstance();
            $connMgr->work($appName, $servers);
        }

        return $config;
    }
}