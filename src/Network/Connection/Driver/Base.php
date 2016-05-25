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
use Zan\Framework\Network\Connection\Pool;


abstract class Base implements Connection
{
    protected $config = null;
    protected $pool = null;
    protected $socket = null;
    protected $engine = null;
    protected $isAsync = false;

    abstract protected function closeSocket();

    public function setPool(ConnectionPool $pool)
    {
        $this->pool = $pool;
    }

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
    
    public function release()
    {
        if(null !== $this->pool){
            return $this->pool->recycle($this);
        }
        
        return $this->closeSocket();
    }
    
    public function close()
    {
        if(null !== $this->pool){  
            $this->pool->remove($this);
        }
        
        $this->closeSocket();
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
}