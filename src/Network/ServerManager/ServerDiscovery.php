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

    private $serviceName;

    /**
     * @var ServerStore
     */
    private $serverStore;

    private $waitIndex = 0;

    public function __construct($config, $serviceName)
    {
        $this->initConfig($config);
        $this->serviceName = $serviceName;
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
        yield $this->get();
        $isWatch = $this->checkIsWatch();
        if (!$isWatch) {
            $this->watch();
        }
    }

    public function get()
    {
        if ($this->isGet()) {
            $servers = $this->getByStore();
            if (null == $servers) {
                Timer::after($this->config['get']['loop_get_time'], [$this, 'get'], $this->getGetServicesJobId());
                return;
            }
        } else {
            $this->serverStore->inc($this->getIsGetKey(), 1);
            $servers = (yield $this->getByEtcd());
        }
        NovaClientConnectionManager::getInstance()->work($this->serviceName, $servers);
    }

    private function isGet()
    {
        $isGet = $this->serverStore->get($this->getIsGetKey());
        if ($isGet > 0) {
            return true;
        }
        return false;
    }

    private function getByStore()
    {
        return $this->serverStore->get($this->getServiceListKey());
    }

    private function getByEtcd()
    {
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' .
            $this->config['get']['protocol'] . ':' .
            $this->config['get']['namespace'] . '/'.
            $this->serviceName;
        $raw = (yield $httpClient->get($uri, [], $this->config['get']['timeout']));
        $servers = (yield $this->parseEtcdData($raw));
        yield $this->save($servers);
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
            $servers[$value['IP'].':'.$value['Port']] = [
                'namespace' => $value['Namespace'],
                'service_name' => $value['SrvName'],
                'host' => $value['IP'],
                'port' => $value['Port'],
                'protocol' => $value['Protocol'],
                'status' => $value['Status'],
                'weight' => $value['Weight'],
                //todo ExtData 暂时不处理 by xiaoniu
            ];
        }
        yield $servers;
    }

    private function save($servers)
    {
        return $this->serverStore->set($this->getServiceListKey(), $servers);
    }

    public function watch()
    {
        $isWatch = $this->checkIsWatch();
        if (!$isWatch) {
            $this->toWatch();
            return;
        }
        Timer::after($this->config['watch']['loop_watch_time'], [$this, 'watch'], $this->getWatchServicesJobId());
    }

    private function checkIsWatch()
    {
        $watchTime = $this->serverStore->get($this->getDoWatchKey());
        $watchTime = $watchTime == null ? 0 : $watchTime;
        if ((time() - $watchTime) > ($this->config['watch']['timeout'] * 1000 + 10)) {
            return false;
        }
        return true;
    }

    public function toWatch()
    {
        $coroutine = $this->watching();
        Task::execute($coroutine);
    }

    public function watching()
    {
        while (true) {
            $this->setDoWatch();
            try {
                $raw = (yield $this->watchEtcd());
                if (null != $raw) {
                    yield $this->update($raw);
                }
            } catch (HttpClientTimeoutException $e) {
            }
        }
    }

    private function setDoWatch()
    {
        return $this->serverStore->set($this->getDoWatchKey(), time());
    }



    private function update($raw)
    {
        $update = (yield $this->parseEtcdData($raw));
        if ([] == $update) {
            yield null;
            return;
        }
        $old = $this->getByStore();
        $offline = array_diff_key($old, $update);
        if ([] != $offline) {
            NovaClientConnectionManager::getInstance()->offline($this->serviceName, $offline);
        }
        $addOnline = array_diff_key($update, $old);
        if ([] != $addOnline) {
            NovaClientConnectionManager::getInstance()->addOnline($this->serviceName, $addOnline);
        }
        $this->serverStore->set($this->getServiceListKey(), $update);
        //todo set waitIndex
    }

    private function watchEtcd()
    {
        $params = $this->waitIndex > 0 ? ['wait' => true, 'recursive' => true, 'waitIndex' => $this->waitIndex] : ['wait' => true, 'recursive' => true];
        $httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
        $uri = $this->config['watch']['uri'] . '/' .
            $this->config['watch']['protocol'] . ':' .
            $this->config['watch']['namespace'] . '/'.
            $this->serviceName;
        yield $httpClient->get($uri, $params, $this->config['watch']['timeout']);
    }

    private function getDoWatchKey()
    {
        return 'last_time_' . $this->serviceName;
    }

    private function getServiceListKey()
    {
        return 'list_' . $this->serviceName;
    }

    private function getIsGetKey()
    {
        return 'get_' . $this->serviceName;
    }

    private function getGetServicesJobId()
    {
        return spl_object_hash($this) . '_get_' . $this->serviceName;
    }
    
    private function getWatchServicesJobId()
    {
        return spl_object_hash($this) . '_watch_' . $this->serviceName;
    }
}


