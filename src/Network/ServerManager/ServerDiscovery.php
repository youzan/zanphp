<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Server\Timer\Timer;

use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;
use Zan\Framework\Network\ServerManager\Exception\ServerDiscoveryEtcdException;
use Zan\Framework\Network\Common\Exception\HttpClientTimeoutException;

use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\ServerManager\LoadBalancingManager;


class ServerDiscovery implements Async
{
    /**
     * @var HttpClient
     */
    private $httpClient;

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
        if (!$this->checkIsWatch()) {
            $this->watch();
        }
        yield LoadBalancingManager::getInstance()->work($servers);
    }

    private function checkIsWatch()
    {
        $watchTime = (yield $this->serverStore->get('last_time'));
        if ((time() - $watchTime) > 3 * $this->config['watch']['loop-time']) {
            return false;
        }
        return true;
    }

    public function get()
    {
        $servers = (yield $this->serverStore->get('list'));
        if (null != $servers) {
            yield $servers;
            return;
        }
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' .
            $this->config['get']['protocol'] . '/' .
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
            $servers[$server['IP'].':'.$server['Port']] = [
                'namespace' => $server['Namespace'],
                'server_name' => $server['SrvName'],
                'ip' => $server['IP'],
                'port' => $server['Port'],
                'protocol' => $server['Protocol'],
                'status' => $server['Status'],
                'weight' => $server['Weight'],
                //todo ExtData 暂时不处理 by xiaoniu
            ];
        }
        yield $servers;
    }

    private function save($servers)
    {
        yield $this->serverStore->set('list', $servers);
    }

    public function watch()
    {
        //绑定心跳检测事件
        Timer::after($this->config['watch']['loop-time'], [$this, 'watch'], spl_object_hash($this));
        $this->setDoWatch();
        try {
            $raw = (yield $this->watchEtcd());
            if (null != $raw) {
                yield $this->update($raw);
                Timer::clearAfterJob(spl_object_hash($this));
                $this->watch();
            }
        } catch (HttpClientTimeoutException $e) {
        }
    }

    private function setDoWatch()
    {
        yield $this->serverStore->set('last_time', time());
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
        yield $this->serverStore->set('list', $update);
        //todo set waitIndex
    }

    private function watchEtcd()
    {
        if (null == $this->httpClient) {
            $this->httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
            $this->httpClient->setTimeout($this->config['watch']['timeout']);
            $this->httpClient->setMethod('GET');
            $params = $this->waitIndex > 0 ? ['wait' => true, 'waitIndex' => $this->waitIndex] : ['wait' => true];
            $body = json_encode($params);
            $this->httpClient->setHeader([
                'Content-Type' => 'application/json',
//                'Connection' => 'keep-alive',
            ]);
            $this->httpClient->setBody($body);
            $uri = $this->config['watch']['uri'] . '/' .
                $this->config['watch']['protocol'] . '/' .
                $this->config['watch']['namespace'] . '/' .
                $this->config['watch']['server_name'];
            $this->httpClient->setUri($uri);
        }
        yield $this;
    }

    public function execute(callable $callback)
    {
        $this->httpClient->setCallback($this->getCallback($callback))->handle();
    }

    private function getCallback(callable $callback)
    {
        return function($response) use ($callback) {
            $jsonData = json_decode($response, true);
            $response = $jsonData ? $jsonData : $response;
            call_user_func($callback, $response);
        };
    }

}


