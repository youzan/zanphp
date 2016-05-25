<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 01:29
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Mysqli as Engine;

class Mysqli extends Base implements Connection
{
    private $classHash = null;
    public function closeSocket()
    {
        return true;
    }
    
    public function heartbeat()
    {
        //绑定心跳检测事件
        $this->classHash = spl_object_hash($this);
        $this->heartbeatLater();
    }

    public function heartbeatLater()
    {
        Timer::after($this->config['pool']['heartbeat-time'], [$this,'heartbeating'], $this->classHash);
    }
    
    public function heartbeating()
    {
        if (!$this->pool->getFreeConnection()->get($this->classHash)) {
            $this->heartbeatLater();
            return ;
        }

        $this->pool->getFreeConnection()->remove($this);
        $coroutine = $this->ping();
        Task::execute($coroutine);
    }
    
    public function ping()
    {
        $engine = new Engine($this);
        try{
            $result = (yield $engine->query('select 1'));
        } catch (MysqliConnectionLostException $e){
            return; 
        }

        $this->release();
        $this->heartbeatLater();
    }
    
    
}