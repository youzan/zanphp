<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 01:29
 */

namespace Zan\Framework\Network\Connection\Driver;


use Zan\Framework\Contract\Network\Connection;

class Mysqli extends Base implements Connection
{
    public function closeSocket()
    {
//        $this->socket->close();
        return true;
    }
    
    public function heartbeat()
    {
        //绑定心跳检测事件
        $key = spl_object_hash($this->getSocket());
        Timer::tick($this->config['keeping-sleep-time'], $key ,
            function($key) {
                if (isset($this->pool->freeConnection[$key])) {
                    $this->pool->freeConnection->remove($this->getSocket());
                    $result = $this->getSocket()->query('select 1');
                    if (!$result) {
                        $this->close();
                    } else {
                        $this->release();
                    }
                }
            });
    }
}