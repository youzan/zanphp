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
use Kdt\Iron\Nova\Foundation\TService;

class NovaClient extends Base implements Connection
{
    private $clientCb;
    
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
        echo "nova client connect to server\n";
    }

    public function onClose(SwooleClient $cli){
        $this->close();
        echo "nova client close\n";
    }

    public function onReceive(SwooleClient $cli, $data) {
        call_user_func($this->clientCb, $data);
    }

    public function onError(SwooleClient $cli){
        $this->close();
        echo "nova client error\n";
    }

    public function setClientCb(callable $cb) {
        $this->clientCb = $cb;
    }

//    public function heartbeat()
//    {
//        Timer::after($this->config['pool']['heartbeat-time'], [$this,'heartbeating'], spl_object_hash($this));
//    }
//
//    public function heartbeating()
//    {
//
//        $this->getSocket();
//    }
//
//    private function ping()
//    {
//
//        $this->heartbeat();
//    }
}