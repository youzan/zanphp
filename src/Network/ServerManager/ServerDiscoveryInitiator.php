<?php
namespace Zan\Framework\Network\ServerManager;

use Kdt\Iron\Nova\Foundation\TService;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;

class ServerDiscoveryInitiator
{
    use Singleton;

    public function init($workerId)
    {
        $this->noNeedDiscovery();
    }

    public function noNeedDiscovery()
    {
        $noNeedDiscovery = Config::get('service_discovery');
        if (empty($noNeedDiscovery)) {
            return;
        }
        if (!isset($noNeedDiscovery['app_names']) || !is_array($noNeedDiscovery['app_names']) || [] === $noNeedDiscovery['app_names']) {
            return;
        }

        foreach ($noNeedDiscovery['app_names'] as $appName) {
            if (!isset($noNeedDiscovery['novaApi'][$appName])) {
                continue;
            }
            if (!isset($noNeedDiscovery['connection'][$appName])) {
                continue;
            }
            $novaConfig = $noNeedDiscovery['novaApi'][$appName];
            $novaConfig += [ "domain" => "com.youzan.service" ];


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
    }
}