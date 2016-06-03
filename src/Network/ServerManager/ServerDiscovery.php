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

use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;
use Zan\Framework\Network\ServerManager\Exception\ServerDiscoveryEtcdException;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;

use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;


class ServerDiscovery
{
    private $config;

    /**
     * @var ServerStore
     */
    private $serverStore;

    private $waitIndex = 0;

    public function __construct()
    {
        $this->initConfig();
        $this->initServerStore();
    }

    private function initConfig()
    {
        $config = Config::get('haunt');
        if (empty($config)) {
            throw new ServerConfigException();
        }
        $this->config = $config;
    }

    private function initServerStore()
    {
        $this->serverStore = ServerStore::getInstance();
    }

    public function start()
    {
        foreach ($this->config['service_name'] as $serviceName) {
            $servers = (yield $this->get($serviceName));
            $isWatch = $this->checkIsWatch($serviceName);
            if (!$isWatch) {
                $this->watch($serviceName);
            }
            NovaClientConnectionManager::getInstance()->work($serviceName, $servers);
        }
    }

    private function checkIsWatch($serviceName)
    {
        $watchTime = $this->serverStore->get($this->getDoWatchKey($serviceName));

        $watchTime = $watchTime == null ? 0 : $watchTime;
        if ((time() - $watchTime) > (3 * $this->config['watch']['timeout'] * 1000)) {
            return false;
        }
        return true;
    }

    public function get($serviceName)
    {
        $servers = $this->serverStore->get($this->getServiceListKey($serviceName));
        if (null !== $servers) {
            yield $servers;
            return;
        }
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' .
            $this->config['get']['protocol'] . ':' .
            $this->config['get']['namespace'] . '/'.
            $serviceName;
        $raw = (yield $httpClient->get($uri, [], $this->config['get']['timeout']));
        $servers = (yield $this->parseEtcdData($raw));
        yield $this->save($serviceName, $servers);
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

    private function save($serviceName, $servers)
    {
        return $this->serverStore->set($this->getServiceListKey($serviceName), $servers);
    }

    public function watch($serviceName)
    {
        $this->setDoWatch($serviceName);
        $coroutine = $this->watching($serviceName);
        Task::execute($coroutine);
    }

    public function watching($serviceName)
    {
        while (true) {
            $this->setDoWatch($serviceName);
            try {
                $raw = (yield $this->watchEtcd($serviceName));
                if (null != $raw) {
                    yield $this->update($serviceName, $raw);
                }
            } catch (HttpClientTimeoutException $e) {
            }
        }
    }

    private function setDoWatch($serviceName)
    {
        return $this->serverStore->set($this->getDoWatchKey($serviceName), time());
    }



    private function update($serviceName, $raw)
    {
        $update = (yield $this->parseEtcdData($raw));
        if ([] == $update) {
            yield null;
            return;
        }
        $old = (yield $this->get($serviceName));
        $offline = array_diff_key($old, $update);
        if ([] != $offline) {
            NovaClientConnectionManager::getInstance()->offline($serviceName, $offline);
        }
        $addOnline = array_diff_key($update, $old);
        if ([] != $addOnline) {
            NovaClientConnectionManager::getInstance()->addOnline($serviceName, $addOnline);
        }
        $this->serverStore->set($this->getServiceListKey($serviceName), $update);
        //todo set waitIndex
    }

    private function watchEtcd($serviceName)
    {
        $params = $this->waitIndex > 0 ? ['wait' => true, 'recursive' => true, 'waitIndex' => $this->waitIndex] : ['wait' => true, 'recursive' => true];
        $httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
        $uri = $this->config['watch']['uri'] . '/' .
            $this->config['watch']['protocol'] . ':' .
            $this->config['watch']['namespace'] . '/'.
            $serviceName;
        yield $httpClient->get($uri, $params, $this->config['watch']['timeout']);
    }

    private function getDoWatchKey($serviceName)
    {
        return 'last_time_' . $serviceName;
    }

    private function getServiceListKey($serviceName)
    {
        return 'list_' . $serviceName;
    }
}


