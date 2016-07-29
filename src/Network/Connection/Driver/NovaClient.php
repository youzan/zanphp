<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/4/21
 * Time: 上午11:46
 */

namespace Zan\Framework\Network\Connection\Driver;

use Zan\Framework\Contract\Network\Connection;
use swoole_client as SwooleClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Foundation\Coroutine\Task;
use Kdt\Iron\Nova\Network\Client as NovaPingClient;
use Zan\Framework\Network\Connection\Exception\NovaClientPingEncodeException;
use Kdt\Iron\Nova\Exception\NetworkException;

use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Network\Connection\ReconnectionPloy;

class NovaClient extends Base implements Connection
{
    private $clientCb;
    protected $isAsync = true;

    protected function closeSocket()
    {
        return true;
    }

    public function init() {
        //set callback
        $this->getSocket()->on('connect', [$this, 'onConnect']);
        $this->getSocket()->on('receive', [$this, 'onReceive']);
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('error', [$this, 'onError']);
    }

    public function onConnect($cli) {
        //put conn to active_pool
        $this->release();
        $this->getPool()->connecting($this);
        $this->heartbeat();
        echo "nova client connect to server\n";
    }

    public function onClose(SwooleClient $cli){
        Timer::clearAfterJob($this->getHeartbeatingJobId());
        $this->close();
        echo "nova client close\n";
    }

    public function onReceive(SwooleClient $cli, $data) {
        call_user_func($this->clientCb, $data);
    }

    public function onError(SwooleClient $cli){
        Timer::clearAfterJob($this->getHeartbeatingJobId());
        $this->close();
        echo "nova client error\n";
    }

    public function setClientCb(callable $cb) {
        $this->clientCb = $cb;
    }
    public function heartbeat()
    {
        Timer::after($this->config['heartbeat-time'], [$this, 'heartbeating'], $this->getHeartbeatingJobId());
    }

    public function heartbeating()
    {
        $time = (Time::current(true) - $this->lastUsedTime) * 1000;
        if ($time >= $this->config['heartbeat-time']) {
            $coroutine = $this->ping();
            Task::execute($coroutine);
        } else {
            Timer::after(($this->config['heartbeat-time'] - $time), [$this, 'heartbeating'], $this->getHeartbeatingJobId());
        }
    }

    public function ping()
    {
        try {
            $client = NovaPingClient::getInstance($this, 'com.youzan.service.test');
            $ping = (yield $client->ping());
        } catch (NetworkException $e) {
            return;
        }
        $this->heartbeat();
    }

    public function close()
    {
        $this->getPool()->remove($this);
        $this->getPool()->reload($this->config);
    }

    public function release()
    {
        $this->getPool()->resetReloadTime($this->config);
    }

    public function setLastUsedTime()
    {
        $this->lastUsedTime = Time::current(true);
    }

    private function getHeartbeatingJobId()
    {
        return spl_object_hash($this) . 'heartbeat';
    }
}
