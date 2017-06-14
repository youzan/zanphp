<?php

namespace Zan\Framework\Network\Connection\Driver;

use Zan\Framework\Contract\Network\Connection;
use swoole_client as SwooleClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Foundation\Coroutine\Task;
use Kdt\Iron\Nova\Network\Client as NovaPingClient;
use Kdt\Iron\Nova\Exception\NetworkException;
use Zan\Framework\Utilities\Types\Time;

class NovaClient extends Base implements Connection
{
    private $clientCb;
    protected $isAsync = true;

    private $serverInfo;

    public function __construct(array $serverInfo = [])
    {
        $this->serverInfo = $serverInfo;
    }

    protected function closeSocket()
    {
        try {
            $this->getSocket()->close();
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }

    public function init() {
        //set callback
        $this->getSocket()->on('connect', [$this, 'onConnect']);
        $this->getSocket()->on('receive', [$this, 'onReceive']);
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('error', [$this, 'onError']);
    }

    public function onConnect(SwooleClient $cli) {
        //put conn to active_pool
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        Timer::clearAfterJob($this->getHeartbeatingJobId());
        $this->release();
        /** @var $pool NovaClientPool */
        $pool = $this->getPool();
        $pool->connecting($this);
        $this->heartbeat();
        $this->inspect("connect to server", $cli);
    }

    public function onClose(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        Timer::clearAfterJob($this->getHeartbeatingJobId());
        $this->close();
        $this->inspect("close", $cli);
    }

    public function onReceive(SwooleClient $cli, $data) {
        try {
            call_user_func($this->clientCb, $data);
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }

    public function onError(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        Timer::clearAfterJob($this->getHeartbeatingJobId());
        $this->close();

        $this->inspect("error", $cli, true);
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
        $this->closeSocket();
        /** @var $pool NovaClientPool */
        $pool = $this->getPool();
        $pool->remove($this);
        $pool->reload($this->config);
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

    private function inspect($desc, SwooleClient $cli, $error = false)
    {
        $info = $this->serverInfo;
        if ($error) {
            $info += [
                "errno" => $cli->errCode,
                "error" => socket_strerror($cli->errCode),
            ];
        }

        $buffer = [];
        foreach ($info as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $buffer[] = "$k=$v";
        }

        sys_echo("nova client $desc [" . implode(", ", $buffer) . "]");
    }
}
