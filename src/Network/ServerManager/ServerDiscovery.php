<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Server\Timer\Timer;

use Zan\Framework\Network\ServerManager\Exception\ServerDiscoveryEtcdException;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;

use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;


class ServerDiscovery
{
    private $config;

    private $module;

    /**
     * @var ServerStore
     */
    private $serverStore;

    private $waitIndex = 0;

    public function __construct($config, $module)
    {
        $this->initConfig($config);
        $this->module = $module;
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

    public function start()
    {
        $this->get();
        $this->watch();
        $this->watchStore();
    }

    public function get()
    {
        if (!$this->lockGetServices()) {
            $servers = $this->getByStore();
            if (null == $servers) {
                Timer::after($this->config['get']['loop_time'], [$this, 'get'], $this->getGetServicesJobId());
            } else {
                NovaClientConnectionManager::getInstance()->work($this->module, $servers);
            }
        } else {
            $coroutine = $this->getByEtcdAndStartConnection();
            Task::execute($coroutine);
        }
    }

    private function getByEtcdAndStartConnection()
    {
        $servers = (yield $this->getByEtcd());
        NovaClientConnectionManager::getInstance()->work($this->module, $servers);
    }

    private function lockGetServices()
    {
        return $this->serverStore->lockGetServices($this->module);
    }

    private function getByStore()
    {
        return $this->serverStore->getServices($this->module);
    }

    private function getByEtcd()
    {
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' .
            $this->config['get']['protocol'] . ':' .
            $this->config['get']['namespace'] . '/'.
            $this->module;
        $raw = (yield $httpClient->get($uri, [], $this->config['get']['timeout']));
        $servers = $this->parseEtcdData($raw);
        $this->saveServices($servers);
        yield $servers;
    }

    private function parseEtcdData($raw)
    {
        if (null === $raw || [] === $raw) {
            throw new ServerDiscoveryEtcdException('get etcd data error');
        }
        if (!isset($raw['node']['nodes']) || count($raw['node']['nodes']) < 1) {
            throw new ServerDiscoveryEtcdException('get etcd can\' find anything');
        }
        $servers = [];
        foreach ($raw['node']['nodes'] as $server) {
            $value = json_decode($server['value'], true);
            $servers[$this->getStoreServicesKey($value['IP'], $value['Port'])] = [
                'namespace' => $value['Namespace'],
                'modules' => $value['SrvName'],
                'host' => $value['IP'],
                'port' => $value['Port'],
                'protocol' => $value['Protocol'],
                'status' => $value['Status'],
                'weight' => $value['Weight'],
                'services' => json_decode($value['ExtData'], true)
            ];
        }
        return $servers;
    }

    private function saveServices($servers)
    {
        return $this->serverStore->setServices($this->module, $servers);
    }

    public function watch()
    {
        if ($this->serverStore->lockWatch($this->module)) {
            $this->toWatch();
            return;
        }
        $isWatch = $this->checkIsWatchTimeout();
        if (!$isWatch) {
            $this->toWatch();
            return;
        }
        Timer::after($this->config['watch']['loop_time'], [$this, 'watch'], $this->getWatchServicesJobId());
    }

    private function checkIsWatchTimeout()
    {
        $watchTime = $this->serverStore->getDoWatchLastTime($this->module);
        $watchTime = $watchTime == null ? 0 : $watchTime;
        if ((time() - $watchTime) > ($this->config['watch']['timeout'] * 1000 + 10)) {
            return false;
        }
        return true;
    }

    private function toWatch()
    {
        $coroutine = $this->watching();
        Task::execute($coroutine);
    }

    private function watching()
    {
        while (true) {
            $this->setDoWatch();
            try {
                $raw = (yield $this->watchEtcd());
                if (null != $raw) {
                    $this->update($raw);
                }
            } catch (HttpClientTimeoutException $e) {
            }
        }
    }

    private function setDoWatch()
    {
        return $this->serverStore->setDoWatchLastTime($this->module);
    }

    private function update($raw)
    {
        $update = $this->parseWatchEtcdData($raw);
        if (null == $update) {
            return;
        }
        if (isset($update['off_line'])) {
            NovaClientConnectionManager::getInstance()->offline($this->module, [$update['off_line']]);
        }
        if (isset($update['add_on_line'])) {
            NovaClientConnectionManager::getInstance()->addOnline($this->module, [$update['add_on_line']]);
        }
        if (isset($update['update'])) {
            NovaClientConnectionManager::getInstance()->update($this->module, [$update['update']]);
        }
    }

    private function parseWatchEtcdData($raw)
    {
        if (null === $raw || [] === $raw) {
            throw new ServerDiscoveryEtcdException('watch etcd data error');
        }
        if (!isset($raw['node']) && !isset($raw['prevNode'])) {
            throw new ServerDiscoveryEtcdException('watch etcd can\' find anything');
        }
        $nowStore = $this->getByStore();
        if (isset($raw['node']['value']) && isset($raw['prevNode']['value'])) {
            $new = json_decode($raw['node']['value'], true);
            $data['update'] = [
                'namespace' => $new['Namespace'],
                'modules' => $new['SrvName'],
                'host' => $new['IP'],
                'port' => $new['Port'],
                'protocol' => $new['Protocol'],
                'status' => $new['Status'],
                'weight' => $new['Weight'],
                'services' => json_decode($new['ExtData'], true)
            ];
            $nowStore[$this->getStoreServicesKey($data['update']['host'], $data['update']['port'])] = $data['update'];
            $this->serverStore->setServices($this->module, $nowStore);
            return $data;
        }
        if (!isset($raw['node']['value'])) {
            $value = json_decode($raw['prevNode']['value'], true);
            $data['off_line'] = [
                'namespace' => $value['Namespace'],
                'modules' => $value['SrvName'],
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
            $this->serverStore->setServices($this->module, $nowStore);
            return $data;
        }
        $value = json_decode($raw['node']['value'], true);
        $data['add_on_line'] = [
            'namespace' => $value['Namespace'],
            'modules' => $value['SrvName'],
            'host' => $value['IP'],
            'port' => $value['Port'],
            'protocol' => $value['Protocol'],
            'status' => $value['Status'],
            'weight' => $value['Weight'],
            'services' => json_decode($value['ExtData'], true)
        ];
        $nowStore[$this->getStoreServicesKey($data['add_on_line']['host'], $data['add_on_line']['port'])] = $data['add_on_line'];
        $this->serverStore->setServices($this->module, $nowStore);
        return $data;
    }

    private function watchEtcd()
    {
        $params = $this->waitIndex > 0 ? ['wait' => true, 'recursive' => true, 'waitIndex' => $this->waitIndex] : ['wait' => true, 'recursive' => true];
        $httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
        $uri = $this->config['watch']['uri'] . '/' .
            $this->config['watch']['protocol'] . ':' .
            $this->config['watch']['namespace'] . '/'.
            $this->module;
        yield $httpClient->get($uri, $params, $this->config['watch']['timeout']);
    }

    public function watchStore()
    {
        Timer::after($this->config['watch_store']['loop_time'], [$this, 'toWatchStore']);
    }

    public function toWatchStore()
    {
        $coroutine = $this->watchingStore();
        Task::execute($coroutine);
    }

    private function watchingStore()
    {
        $storeServices = $this->serverStore->getServices($this->module);
        $connectionsConfig = NovaClientConnectionManager::getInstance()->getPool($this->module)->getConfig();
        $onLine = $offLine = $update = [];
        $useServices = NovaClientConnectionManager::getInstance()->getSeverConfig($this->module);
        foreach ($connectionsConfig as $key => $service) {
            if (!isset($storeServices[$key])) {
                $offLine[$key] = $service;
            } elseif (isset($useServices[$key]) && $service != $useServices[$key]) {
                $update[$key] = $service;
            }
        }
        foreach ($storeServices as $key => $service) {
            if (!isset($connectionsConfig[$key])) {
                $onLine[$key] = $service;
            }
        }
        if ([] != $offLine) {
            NovaClientConnectionManager::getInstance()->offline($this->module, $offLine);
        }
        if ([] != $onLine) {
            NovaClientConnectionManager::getInstance()->addOnline($this->module, $onLine);
        }
        if ([] != $update) {
            NovaClientConnectionManager::getInstance()->update($this->module, $update);
        }
        $this->watchStore();
    }

    private function getStoreServicesKey($host, $port)
    {
        return $host . ':' . $port;
    }

    private function getGetServicesJobId()
    {
        return spl_object_hash($this) . '_get_' . $this->module;
    }
    
    private function getWatchServicesJobId()
    {
        return spl_object_hash($this) . '_watch_' . $this->module;
    }
}


