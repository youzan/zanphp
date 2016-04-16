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
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Network\Connection\Driver\Mysqli;
use Zan\Framework\Network\Connection\Driver\Http;
use Zan\Framework\Network\Connection\Driver\Redis;
use Zan\Framework\Network\Connection\Driver\Syslog;
use Zan\Framework\Utilities\Types\ObjectArray;

class Pool implements ConnectionPool
{

    private $freeConnection = null;

    private $activeConnection = null;

    private $poolConfig = null;

    private $factory = null;

    private $type = null;




    public function __construct(ConnectionFactory $connectionFactory, array $config, $type)
    {
        $this->poolConfig = $config;
        $this->factory = $connectionFactory;
        $this->type = $type;
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
            $this->createConnect();
        }

    }

    //创建连接
    private function createConnect()
    {
        //todo 创建链接,存入数组
        $mysqlConnection = $this->factory->create();
        switch($this->type) {
            case 'Mysqli':
                $connection = new Mysqli();
            case 'Http':
                $connection = new Http();
            case 'Redis':
                $connection = new Redis();
            case 'Syslog':
                $connection = new Syslog();
            default:
            {
                //do nothing
            }
        }
        $connection->setSocket($mysqlConnection . $i);
        $this->freeConnection->push($connection);
        $connection->setPool($this);
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
        //补充删除被删除连接
        $this->createConnect();

    }
}