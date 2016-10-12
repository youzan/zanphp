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
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Network\Connection\Pool;
use Zan\Framework\Network\Connection\ReconnectionPloy;
use Zan\Framework\Network\Server\Timer\Timer;


class Tcp extends Base implements Connection
{
    private $clientCb;
    protected $isAsync = true;
    
    public function closeSocket()
    {
        try {
            $this->getSocket()->close();
        } catch (\Exception $e) {
            //todo log
        }
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
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->release();

        ReconnectionPloy::getInstance()->connectSuccess(spl_object_hash($this));
        echo "tcp client connect to server\n";
    }

    public function onClose(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->close();
        echo "tcp client close\n";
    }

    public function onReceive(SwooleClient $cli, $data) {
        call_user_func($this->clientCb, $data);
    }

    public function onError(SwooleClient $cli){
        Timer::clearAfterJob($this->getConnectTimeoutJobId());
        $this->close();
        echo "tcp client error\n";
    }

    public function onConnectTimeout(){
        /* @var $pool Pool */
        $pool = $this->pool;
        $evtName = $pool->getPoolConfig()['pool']['pool_name'] . '_connect_timeout';
        Event::fire($evtName, [], false);
        $pool->waitNum = $pool->waitNum >0 ? $pool->waitNum-- : 0 ;
        echo "tcp client connect timeout\n";
    }

    public function setClientCb(callable $cb) {
        $this->clientCb = $cb;
    }
}