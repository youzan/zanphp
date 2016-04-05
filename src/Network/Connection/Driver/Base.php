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

abstract class Base implements Connection
{
    protected $config = null;
    protected $pool = null;
    protected $socket = null;

    abstract protected function closeSocket();

    public function setPool(ConnectionPool $pool)
    {
        $this->pool = $pool;
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
            return $this->pool->release($this);
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

    public function ping()
    {
        return null;
    }
}