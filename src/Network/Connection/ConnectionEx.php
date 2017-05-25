<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/28
 * Time: 上午11:07
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\Connection;

class ConnectionEx implements Connection
{
    public $connEx;
    public $poolEx;

    public function __construct($connEx, PoolEx $pool)
    {
        $this->connEx = $connEx;
        $this->poolEx = $pool;
    }

    public function getSocket()
    {
        return $this->connEx;
    }

    public function release()
    {
        return $this->poolEx->release($this->connEx);
    }

    public function getEngine()
    {
        return $this->poolEx->poolType;
    }

    public function getConfig()
    {
        return $this->poolEx->config;
    }

    public function close()
    {
        return $this->poolEx->close($this->connEx);
    }

    public function heartbeat() { }
}