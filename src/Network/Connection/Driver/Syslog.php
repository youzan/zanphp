<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:36
 */

namespace Zan\Framework\Network\Connection\Driver;

use swoole_client as SwooleClient;
use Zan\Framework\Contract\Network\Connection;

class Syslog extends Base implements Connection
{
    private $postData;
    protected $isAsync = true;

    public function init()
    {
        //set callback
        $this->getSocket()->on('connect', [$this, 'onConnect']);
        $this->getSocket()->on('close', [$this, 'onClose']);
        $this->getSocket()->on('error', [$this, 'onError']);
    }

    public function send($log)
    {
        $this->postData = $log . "\n";
        $this->getSocket()->send($this->postData);
        $this->release();
    }

    public function onConnect($cli)
    {
        $this->release();
    }

    public function onClose(SwooleClient $cli)
    {
        $this->close();
    }

    public function onError(SwooleClient $cli)
    {
        $this->close();
    }

    protected function closeSocket()
    {
        return true;
    }
}
