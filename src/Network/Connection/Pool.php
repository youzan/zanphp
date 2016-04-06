<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/4
 * Time: 01:20
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\ConnectionFactory;
use Zan\Framework\Contract\Network\ConnectionPool;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\Driver\Mysqli;
use Zan\Framework\Utilities\Types\ObjectArray;

class Pool implements ConnectionPool
{

    private $freeConnection = null;

    private $activeConnection = null;

    private $poolConfig = null;

    private $factory = null;




    public function __construct(ConnectionFactory $connectionFactory, array $config)
    {
        $this->poolConfig = $config;
        $this->factory = $connectionFactory;
        $this->init();
    }

    public function init()
    {
        //todo 读取配置文件
        $initConnection = $this->poolConfig['init-connection'];
        $this->freeConnection = new ObjectArray();
        $this->activeConnection = new ObjectArray();
        for ($i=0; $i<$initConnection; $i++) {
            //todo 创建链接,存入数组
            $mysqlConnection = $this->factory->create();
            $connection = new Mysqli();
            $connection->setPool($this);
            $connection->setSocket($mysqlConnection);
            $this->freeConnection->push($connection);
        }
    }
    
    public function reload(array $config)
    {
        
    }

    public function get()
    {
        if (count($this->activeConnection) < $this->poolConfig['maximum-connection-count']) {
            if (count($this->freeConnection) > 0) {
                $conn = $this->freeConnection->pop();
            }
        } else {
            return null;
        }
        if ($conn) {
            $this->activeConnection->push($conn);
        }
//        deferRelease($conn);
        return $conn;
    }
    
    public function recycle(Connection $conn)
    {
        $this->freeConnection->push($conn);
        $this->activeConnection->remove($conn);
        if (count($this->freeConnection) == 1) {
            //唤醒等待事件
            $evtName = '' . '_free';
            Event::fire($evtName, [], false);
        }
    }
    
    public function remove(Connection $conn)
    {
        $this->freeConnection->remove($conn);
        $this->activeConnection->remove($conn);
    }
}