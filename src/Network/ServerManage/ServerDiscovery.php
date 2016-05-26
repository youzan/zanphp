<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManage;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Network\Server\Timer\Timer;

use Zan\Framework\Network\ServerManage\Exception\ServerConfigException;

use Zan\Framework\Network\ServerManage\ServerStore;
use Zan\Framework\Network\ServerManage\LoadBalancing;


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

    private $classHash;

    public function __construct()
    {
        $this->initConfig();
        $this->initServerStore();
    }

    private function initConfig()
    {
        $config = Config::get('');
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
        yield $this->get();
        if (!$this->checkIsWatch()) {
            $this->watch();
        }
        yield LoadBalancing::getInstance()->work();
    }

    public function get()
    {
        $servers = (yield $this->serverStore->get('list'));
        if (null != $servers) {
            yield $servers;
            return;
        }
        $httpClient = new HttpClient($this->config['get']['host'], $this->config['get']['port']);
        $uri = $this->config['get']['uri'] . '/' . $this->config['get']['protocol'] . '/' . $this->config['get']['namespace'] . '/'.  $this->config['get']['server_ame'];
        $raw = (yield $httpClient->get($uri, [], $this->config['get']['timeout']));
        $servers = (yield $this->parseServersData($raw));
        yield $this->serverStore->set('list', $servers);
        yield $servers;
    }

    private function parseServersData($servers)
    {

    }

    public function watch()
    {
        //绑定心跳检测事件
        $this->classHash = spl_object_hash($this);
        Timer::after($this->config['watch']['loop-time'], [$this, 'watching'], $this->classHash);
    }

    private function watching()
    {
        $response = (yield $this->watchEtcd());
        yield $this->serverStore->set('last_time', time());
        if ($response) {

        }
    }

    private function watchEtcd()
    {
        if (null == $this->httpClient) {
            $this->httpClient = new HttpClient($this->config['watch']['host'], $this->config['watch']['port']);
            $this->httpClient->setTimeout($this->config['watch']['timeout']);
            $this->httpClient->setMethod('GET');
            $body = json_encode([]);
            $this->httpClient->setHeader([
                'Content-Type' => 'application/json',
                'Connection' => 'keep-alive'
            ]);
            $this->httpClient->setBody($body);
            $uri = $this->config['watch']['uri'] . '/' . $this->config['watch']['protocol'] . '/' . $this->config['watch']['namespace'] . '/'.  $this->config['watch']['server_ame'] . '?wait=true';
            $this->httpClient->setUri($uri);
        }
        yield $this;
    }

    private function checkIsWatch()
    {
        $watchTime = yield $this->serverStore->get('last_time');
        if ($watchTime > 3 * $this->config['watch']['loop-time']) {
            return false;
        }
        return true;
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

    private function save()
    {

    }

    private function update()
    {

    }
}


