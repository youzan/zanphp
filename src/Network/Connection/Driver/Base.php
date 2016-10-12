<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 00:31
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Contract\Network\ConnectionPool;
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Network\Connection\Pool;


abstract class Base implements Connection
{
    protected $config = null;
    protected $pool = null;
    protected $socket = null;
    protected $engine = null;
    protected $isAsync = false;
    protected $isClose = false;
    protected $isReleased = false;
    public $lastUsedTime=0;

    abstract protected function closeSocket();

    public function setPool($pool)
    {
        $this->pool = $pool;
    }

    /**
     * @return ConnectionPool
     */
    public function getPool()
    {
        return $this->pool;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getSocket()
    {
        return $this->socket;
    }
    
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    public function unsetSocket()
    {
        unset($this->socket);
        $this->socket = null;
    }

    public function setUnReleased()
    {
        $this->isReleased = false;
    }

    public function release()
    {
        if (true === $this->isReleased) {
            return;
        }
        if (null !== $this->pool) {
            $this->isReleased = true;
            return $this->pool->recycle($this);
        }

        $this->closeSocket();
    }
    
    public function close()
    {
        if (true === $this->isClose) {
            return;
        }
        $this->isClose = true;

        $this->closeSocket();
        if (null !== $this->pool) {
            $this->pool->remove($this);
        }
    }

    public function heartbeat()
    {
    }

    public function setEngine($engine)
    {
        $this->engine= $engine;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function setIsAsync($isAsync) {
        $this->isAsync = $isAsync;
    }

    public function getIsAsync() {
        return $this->isAsync;
    }

    public function getConnectTimeoutJobId()
    {
        return spl_object_hash($this) . '_connect_timeout';
    }

    public function onConnectTimeout()
    {
        $pool = $this->pool;

        if ($pool instanceof Pool) {
            $evtName = $pool->getPoolConfig()['pool']['pool_name'] . '_connect_timeout';
            Event::fire($evtName, [], false);
            $pool->waitNum = $pool->waitNum >0 ? $pool->waitNum-- : 0 ;
        } else if ($pool instanceof NovaClientPool) {
            // do nothing
        }

        $client = substr(static::class, strrpos(static::class, "\\") + 1);
        echo "$client client connect timeout\n";
    }
}