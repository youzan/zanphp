<?php

namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\ServerManager\Exception\ServerDiscoveryEtcdException;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Json;
use Zan\Framework\Utilities\Types\Time;

class ServerDiscovery
{
    // {{{ from haunt_sdk/nameserv_agent const_define.go
    const SRV_UNINIT = 0;
    const SRV_STATUS_OK = 1;
    const SRV_STATUS_NOT_OK = 2;
    const SRV_STATUS_UNREG = 3;
    //}}}

    const DEFAULT_PROTOCOL = "nova";
    const DEFAULT_NAMESPACE = "com.youzan.service";

    const DEFAULT_DISCOVER_TIMEOUT = 3000;
    const DEFAULT_LOOKUP_TIMEOUT = 30000;
    const DEFAULT_WATCH_TIMEOUT = 30000;

    private $config;

    private $appName;

    private $protocol;

    private $namespace;

    /**
     * @var ServerStore
     */
    private $serverStore;

    const DISCOVERY_LOOP_TIME   = 1000;

    const WATCH_LOOP_TIME       = 5000;

    const WATCH_STORE_LOOP_TIME = 1000;

    public function __construct($config, $appName, $protocol, $namespace)
    {
        $this->initConfig($config);
        $this->appName = $appName;
        $this->protocol = $protocol;
        $this->namespace = $namespace;
        $this->initServerStore();
    }

    private function initConfig($config)
    {
        $this->config = $config;
    }

    private function initServerStore()
    {
        $this->serverStore = ServerStore::getInstance();
    }

    public function workByEtcd()
    {
        $this->discoverByEtcdTask();
        $this->watchByEtcdTask();
    }

    public function workByStore()
    {
        $this->discoverByStore();
    }

    public function discoverByEtcdTask()
    {
        $coroutine = $this->discoveringByEtcd();
        Task::execute($coroutine);
    }

    public function discoverByStore()
    {
        $servers = $this->getByStore();
        if (null == $servers) {
            $discoveryLoopTime = Arr::get($this->config, "discovery.loop_time", self::DISCOVERY_LOOP_TIME);
            Timer::after($discoveryLoopTime, [$this, 'discoverByStore'], $this->getGetServicesJobId());
        } else {
            NovaClientConnectionManager::getInstance()->work($this->appName, $servers);
            $this->checkWatchingByEtcd();
            $this->watchByStore();
        }
    }

    private function discoveringByEtcd()
    {
        try {
            $servers = (yield $this->getByEtcd());
            NovaClientConnectionManager::getInstance()->work($this->appName, $servers);
            return;
        } catch (\Throwable $ex) {
        } catch (\Exception $ex) {}

        sys_error("service discovery by etcd fail [app=$this->appName]");
        echo_exception($ex);

        Timer::after(2000, function() {
            $co = $this->discoveringByEtcd();
            Task::execute($co);
        });
    }

    private function getByStore()
    {
        return $this->serverStore->getServices($this->appName);
    }

    private function getByEtcd()
    {
        $node = ServerRegister::getRandEtcdNode();

        $httpClient = new HttpClient($node["host"], $node["port"]);
        $uri = $this->buildEtcdUri();

        $discoveryTimeout = Arr::get($this->config, "discovery.timeout", self::DEFAULT_DISCOVER_TIMEOUT);
        $response = (yield $httpClient->get($uri, [], $discoveryTimeout));
        $raw = $response->getBody();
        $jsonData = Json::decode($raw, true);
        $result = $jsonData ? $jsonData : $raw;

        $servers = $this->parseEtcdData($result);
        $this->saveServices($servers);
        yield $servers;
    }

    private function parseEtcdData($raw)
    {
        if (null === $raw || [] === $raw) {
            throw new ServerDiscoveryEtcdException("Service Discovery can not find key of the app {$this->appName}");
        }

        if (!isset($raw['node']['nodes']) || count($raw['node']['nodes']) < 1) {
            if (isset($raw["index"])) {
                $this->serverStore->setServiceWaitIndex($this->appName, $raw["index"]);
            }
            $detail = null;
            if (isset($raw["errorCode"])) {
                $detail = "[errno={$raw["errorCode"]}, msg={$raw["message"]}, cause={$raw["cause"]}]";
            }
            throw new ServerDiscoveryEtcdException("Service Discovery can not find node of the app {$this->appName} $detail");
        }
        $servers = [];
        $waitIndex = 0;
        foreach ($raw['node']['nodes'] as $server) {
            // 无ttl为无效节点
            // NOTICE!!! 此处可能有坑，万一有set了etcd的key有没有ttl参数!!!
            if (!isset($server["ttl"])) {
                continue;
            }

            $value = json_decode($server['value'], true);

            // Status !== 1 无效节点
            if ($value["Status"] !== self::SRV_STATUS_OK) {
                continue;
            }

            // 只关注 订阅 domain
            if ($value['Namespace'] !== $this->namespace) {
                continue;
            }

            $servers[$this->getStoreServicesKey($value['IP'], $value['Port'])] = [
                'namespace' => $value['Namespace'],
                'app_name' => $value['SrvName'],
                'host' => $value['IP'],
                'port' => $value['Port'],
                'protocol' => $value['Protocol'],
                'status' => $value['Status'],
                'weight' => $value['Weight'],
                'services' => json_decode($value['ExtData'], true) ?: []
            ];
            $waitIndex = $waitIndex >= $server['modifiedIndex'] ? $waitIndex : $server['modifiedIndex'];
        }
        $waitIndex = $waitIndex + 1;
        $this->serverStore->setServiceWaitIndex($this->appName, $waitIndex);

        if (empty($servers)) {
            throw new ServerDiscoveryEtcdException("Service Discovery can not find any valid node of the app $this->appName");
        }

        return $servers;
    }

    private function saveServices($servers)
    {
        return $this->serverStore->setServices($this->appName, $servers);
    }

    //watch

    public function watchByEtcdTask()
    {
        $coroutine = $this->watchByEtcd();
        Task::execute($coroutine);
    }

    private function watchByEtcd()
    {
        while (true) {
            $this->setDoWatchByEtcd();
            try {
                $raw = (yield $this->watchingByEtcd());
                if (null != $raw) {
                    $this->updateServersByEtcd($raw);
                }
            } catch (HttpClientTimeoutException $e) {
                yield taskSleep(50);
            } catch (\Throwable $t) {
                echo_exception($t);
                yield taskSleep(50);
            } catch (\Exception $ex) {
                echo_exception($ex);
                yield taskSleep(50);
            }
        }
    }

    private function setDoWatchByEtcd()
    {
        return $this->serverStore->setDoWatchLastTime($this->appName);
    }

    private function watchingByEtcd()
    {
        $waitIndex = $this->serverStore->getServiceWaitIndex($this->appName);
        $params = $waitIndex > 0 ? ['wait' => true, 'recursive' => true, 'waitIndex' => $waitIndex] : ['wait' => true, 'recursive' => true];

        $node = ServerRegister::getRandEtcdNode();
        $httpClient = new HttpClient($node["host"], $node["port"]);
        $uri = $this->buildEtcdUri();

        $watchTimeout = Arr::get($this->config, "watch.timeout", self::DEFAULT_WATCH_TIMEOUT);
        $response = (yield $httpClient->get($uri, $params, $watchTimeout));
        $raw = $response->getBody();
        $jsonData = Json::decode($raw, true);
        $result = $jsonData ? $jsonData : $raw;

        yield $result;
    }

    private function updateServersByEtcd($raw)
    {
        $isOutdated = $this->checkWaitIndexIsOutdatedCleared($raw);
        if (true == $isOutdated) {
            return;
        }
        $update = $this->parseWatchByEtcdData($raw);
        if (null == $update) {
            return;
        }

        if (isset($update['off_line'])) {
            sys_echo("watch by etcd nova client off line " . $this->appName . " host:" . $update['off_line']['host'] . " port:" . $update['off_line']['port']);
            NovaClientConnectionManager::getInstance()->offline($this->appName, [$update['off_line']]);
        }
        if (isset($update['add_on_line'])) {
            sys_echo("watch by etcd nova client add on line " . $this->appName . " host:" . $update['add_on_line']['host'] . " port:" . $update['add_on_line']['port']);
            NovaClientConnectionManager::getInstance()->addOnline($this->appName, [$update['add_on_line']]);
        }
        if (isset($update['update'])) {
            sys_echo("watch by etcd nova client update service " . $this->appName . " host:" . $update['update']['host'] . " port:" . $update['update']['port']);
            NovaClientConnectionManager::getInstance()->update($this->appName, [$update['update']]);
        }
    }

    private function checkWaitIndexIsOutdatedCleared($raw)
    {
        if (isset($raw['errorCode']) && isset($raw['index']) && $raw['index'] > 0) {
            $waitIndex = $raw['index'] + 1;
            $this->serverStore->setServiceWaitIndex($this->appName, $waitIndex);
            return true;
        }
        return false;
    }

    private function parseWatchByEtcdData($raw)
    {
        if (null === $raw || [] === $raw) {
            throw new ServerDiscoveryEtcdException('watch Service Discovery data error app_name :'.$this->appName);
        }
        if (!isset($raw['node']) && !isset($raw['prevNode'])) {
            if (isset($raw["index"])) {
                $this->serverStore->setServiceWaitIndex($this->appName, $raw["index"]);
            }
            $detail = null;
            if (isset($raw["errorCode"])) {
                $detail = "[errno={$raw["errorCode"]}, msg={$raw["message"]}, cause={$raw["cause"]}]";
            }
            throw new ServerDiscoveryEtcdException("watch Service Discovery can not find anything app_name:{$this->appName} $detail");
        }

        $nowStore = $this->getByStore();
        $waitIndex = $this->serverStore->getServiceWaitIndex($this->appName);

        // 是否可以根据 action 判断 ???
        // $action = $raw['action'];

        // 注意: 非dev环境haunt, 因为下线节点不从etcd摘除, 理论上永远只会进去update分支
        // 1. 更新: 存在 node  && 存在 prevNode
        if (isset($raw['node']['value']) && isset($raw['prevNode']['value'])) {
            if (isset($raw['node']['modifiedIndex'])) {
                $waitIndex = $raw['node']['modifiedIndex'] >= $waitIndex ? $raw['node']['modifiedIndex'] : $waitIndex;
                $waitIndex = $waitIndex + 1;
                $this->serverStore->setServiceWaitIndex($this->appName, $waitIndex);
            }

            $new = json_decode($raw['node']['value'], true);

            // 只关注 订阅 domain
            if ($new['Namespace'] !== $this->namespace) {
                return null;
            }

            $nowAlive = isset($raw['node']['ttl']) && $raw['node']['ttl'] > 0;
            if (!$nowAlive) {
                return $this->serverOffline($nowStore, $new);
            }

            $preAlive = isset($raw['prevNode']['ttl']) && $raw['prevNode']['ttl'] > 0;
            if (!$preAlive && $nowAlive) {
                return $this->serverOnline($nowStore, $new);
            }

            $nowStatus = $new['Status'];
            if ($nowStatus !== self::SRV_STATUS_OK) {
                return $this->serverOffline($nowStore, $new);
            }

            $pre = json_decode($raw['prevNode']['value'], true);
            $prevStatus = $pre["Status"];
            if ($prevStatus !== self::SRV_STATUS_OK && $nowStatus === self::SRV_STATUS_OK) {
                return $this->serverOnline($nowStore, $new);
            }

            return $this->serverUpdate($nowStore, $new);

            /*
            $storeKey = $this->getStoreServicesKey($new['IP'], $new['Port']);
            if (isset($nowStore[$storeKey])) {
                $nowServer = $nowStore[$storeKey];
                $oldStatus = $nowServer["status"];
                $newStatus = $new['Status'];
                if ($oldStatus === self::SRV_STATUS_OK && $newStatus !== self::SRV_STATUS_OK) {
                    return $this->serverOffline($nowStore, $new);
                } else if ($oldStatus !== self::SRV_STATUS_OK && $newStatus === self::SRV_STATUS_OK) {
                    return $this->serverOnline($nowStore, $new);
                }
            }
            */
        }

        // 注意: 理论上node与prenode应该都存在, 这里兼容不同环境haunt的差异

        // 2. 离线: 只存在 prevNode node 不存在 node
        if (!isset($raw['node']['value'])) {
            if (isset($raw['node']['modifiedIndex'])) {
                $waitIndex = $raw['node']['modifiedIndex'] >= $waitIndex ? $raw['node']['modifiedIndex'] : $waitIndex;
            }
            if (isset($raw['prevNode']['modifiedIndex'])) {
                $waitIndex = $raw['prevNode']['modifiedIndex'] >= $waitIndex ? $raw['prevNode']['modifiedIndex'] : $waitIndex;
            }
            $waitIndex = $waitIndex + 1;
            $this->serverStore->setServiceWaitIndex($this->appName, $waitIndex);

            $value = json_decode($raw['prevNode']['value'], true);
            // 只关注 订阅 domain
            if ($value['Namespace'] !== $this->namespace) {
                return null;
            }
            return $this->serverOffline($nowStore, $value);
        }

        // 3. 上线: 不存在 prevNode && 只存在 node
        if (isset($raw['node']['modifiedIndex'])) {
            $waitIndex = $raw['node']['modifiedIndex'] >= $waitIndex ? $raw['node']['modifiedIndex'] : $waitIndex;
            $waitIndex = $waitIndex + 1;
            $this->serverStore->setServiceWaitIndex($this->appName, $waitIndex);
        }

        $value = json_decode($raw['node']['value'], true);
        // 只关注 订阅 domain
        if ($value['Namespace'] !== $this->namespace) {
            return null;
        }
        return $this->serverOnline($nowStore, $value);
    }

    public function checkWatchingByEtcd()
    {
        $isWatching = $this->checkIsWatchingByEtcdTimeout();
        if (!$isWatching) {
            $this->watchByEtcdTask();
            return;
        }
        $watchLoopTime = Arr::get($this->config, "watch.loop_time", self::WATCH_LOOP_TIME);
        Timer::after($watchLoopTime, [$this, 'checkWatchingByEtcd'], $this->getWatchServicesJobId());
    }

    private function checkIsWatchingByEtcdTimeout()
    {
        $watchTime = $this->serverStore->getDoWatchLastTime($this->appName);
        if (null === $watchTime) {
            return true;
        }
        $watchTimeout = Arr::get($this->config, "watch.timeout", self::DEFAULT_WATCH_TIMEOUT);
        if ((Time::current(true) - $watchTime) > ($watchTimeout + 10)) {
            return false;
        }
        return true;
    }

    public function watchByStore()
    {
        $watchStoreLoopTime = Arr::get($this->config, "watch_store.loop_time", self::WATCH_STORE_LOOP_TIME);
        Timer::after($watchStoreLoopTime, [$this, 'watchByStoreTask']);
    }

    public function watchByStoreTask()
    {
        $coroutine = $this->watchingByStore();
        Task::execute($coroutine);
    }

    private function watchingByStore()
    {
        $storeServices = $this->serverStore->getServices($this->appName);
        $onLine = $offLine = $update = [];
        $useServices = NovaClientConnectionManager::getInstance()->getServersFromAppNameToServerMap($this->appName);
        if (!empty($storeServices)) {
            foreach ($useServices as $key => $service) {
                if (!isset($storeServices[$key])) {
                    $offLine[$key] = $service;
                } elseif (isset($useServices[$key]) && $service != $useServices[$key]) {
                    $update[$key] = $service;
                }
            }
            foreach ($storeServices as $key => $service) {
                if (!isset($useServices[$key])) {
                    $onLine[$key] = $service;
                }
            }
            if ([] != $offLine) {
                NovaClientConnectionManager::getInstance()->offline($this->appName, $offLine);
            }
            if ([] != $onLine) {
                NovaClientConnectionManager::getInstance()->addOnline($this->appName, $onLine);
            }
            if ([] != $update) {
                NovaClientConnectionManager::getInstance()->update($this->appName, $update);
            }
        } else {
            if (!empty($useServices)) {
                NovaClientConnectionManager::getInstance()->offline($this->appName, $useServices);
            }
        }
        $this->watchByStore();
    }

    private function getStoreServicesKey($host, $port)
    {
        return $host . ':' . $port;
    }

    private function getGetServicesJobId()
    {
        return spl_object_hash($this) . '_get_' . $this->appName;
    }
    
    private function getWatchServicesJobId()
    {
        return spl_object_hash($this) . '_watch_' . $this->appName;
    }

    private function buildEtcdUri()
    {
        return "/v2/keys/$this->protocol:$this->namespace/$this->appName";
    }

    private function serverOnline($nowStore, $value)
    {
        $data['add_on_line'] = [
            'namespace' => $value['Namespace'],
            'app_name' => $value['SrvName'],
            'host' => $value['IP'],
            'port' => $value['Port'],
            'protocol' => $value['Protocol'],
            'status' => $value['Status'],
            'weight' => $value['Weight'],
            'services' => json_decode($value['ExtData'], true)
        ];

        $nowStore[$this->getStoreServicesKey($data['add_on_line']['host'], $data['add_on_line']['port'])] = $data['add_on_line'];
        $this->serverStore->setServices($this->appName, $nowStore);

        return $data;
    }

    private function serverOffline($nowStore, $value)
    {
        $data['off_line'] = [
            'namespace' => $value['Namespace'],
            'app_name' => $value['SrvName'],
            'host' => $value['IP'],
            'port' => $value['Port'],
            'protocol' => $value['Protocol'],
            'status' => $value['Status'],
            'weight' => $value['Weight'],
            'services' => json_decode($value['ExtData'], true)
        ];

        if (isset($nowStore[$this->getStoreServicesKey($data['off_line']['host'], $data['off_line']['port'])])) {
            unset($nowStore[$this->getStoreServicesKey($data['off_line']['host'], $data['off_line']['port'])]);
        }
        $this->serverStore->setServices($this->appName, $nowStore);
        return $data;
    }

    private function serverUpdate($nowStore, $new)
    {
        $data['update'] = [
            'namespace' => $new['Namespace'],
            'app_name' => $new['SrvName'],
            'host' => $new['IP'],
            'port' => $new['Port'],
            'protocol' => $new['Protocol'],
            'status' => $new['Status'],
            'weight' => $new['Weight'],
            'services' => json_decode($new['ExtData'], true)
        ];

        $nowStore[$this->getStoreServicesKey($data['update']['host'], $data['update']['port'])] = $data['update'];
        $this->serverStore->setServices($this->appName, $nowStore);
        return $data;
    }
}