<?php

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Contract\Network\ConnectionFactory;
use Zan\Framework\Contract\Network\ConnectionPool;


abstract class Base implements Connection
{
    protected $config = null;
    /** @var ConnectionPool  */
    protected $pool = null;
    /** @var ConnectionFactory */
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

    /**
     * @return ConnectionFactory
     */
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
        $client = lcfirst(substr(static::class, strrpos(static::class, "\\") + 1));
        sys_error("$client client connect timeout " . $this->getConnString());
    }

    public function getConnString()
    {
        if (isset($this->config["path"])) {
            return "[path={$this->config["path"]}]";
        } else if (isset($this->config["host"]) && isset($this->config["port"])){
            return "[host={$this->config["host"]}, port={$this->config["port"]}]";
        } else {
            return "";
        }
    }
}