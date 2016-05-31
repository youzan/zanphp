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
use Zan\Framework\Network\ServerManager\LoadBalancingManager;
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
        $servers = (yield $this->get());
        $isWatch = $this->checkIsWatch();
        if (!$isWatch) {
            $this->watch();
        }
        LoadBalancingManager::getInstance()->work($servers);
    }

    private function checkIsWatch()
    {
        $watchTime = $this->serverStore->get('last_time');
        $watchTime = $watchTime == null ? 0 : $watchTime;
        if ((time() - $watchTime) > (3 * $this->config['watch']['loop-time'])) {
            return false;
        }
        return false;
    }

    public function get()
    {
        $servers = $this->serverStore->get('list');
        if (null !== $servers) {
            yield $servers;
            return;
        }
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' .
            $this->config['get']['protocol'] . ':' .
            $this->config['get']['namespace'] . '/'.
            $this->config['get']['server_name'];
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
                'server_name' => $value['SrvName'],
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
        return $this->serverStore->set('list', $servers);
    }

    public function watch()
    {
        $coroutine = $this->watching();
        Task::execute($coroutine);
    }

    public function watching()
    {
        try {
            $raw = (yield $this->watchEtcd());
//            if (null != $raw) {
//                yield $this->update($raw);
//            }
            var_dump($raw);
        } catch (HttpClientTimeoutException $e) {
        }
        $this->after();
    }

    public function after()
    {
        Timer::after($this->config['watch']['loop-time'], [$this, 'watching'], spl_object_hash($this));
    }

    private function setDoWatch()
    {
        return $this->serverStore->set('last_time', time());
    }

    private function update($raw)
    {
        $update = (yield $this->parseEtcdData($raw));
        if ([] == $update) {
            yield null;
            return;
        }
        $old = (yield $this->get());
        $offline = array_diff_key($old, $update);
        if ([] != $offline) {
            yield LoadBalancingManager::getInstance()->offline($offline);
        }
        $addOnline = array_diff_key($update, $old);
        if ([] != $addOnline) {
            yield LoadBalancingManager::getInstance()->addOnline($addOnline);
        }
        $this->serverStore->set('list', $update);
        //todo set waitIndex
    }

    private function watchEtcd()
    {
        $params = $this->waitIndex > 0 ? ['wait' => true, 'recursive' => true, 'waitIndex' => $this->waitIndex] : ['wait' => true, 'recursive' => true];
        $httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
        $uri = $this->config['watch']['uri'] . '/' .
            $this->config['watch']['protocol'] . ':' .
            $this->config['watch']['namespace'] . '/'.
            $this->config['watch']['server_name'];
        yield $httpClient->get($uri, $params, $this->config['watch']['timeout']);
    }
}


