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
use Zan\Framework\Utilities\Types\ObjectArray;
use Zan\Framework\Utilities\Types\Time;

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
        $initConnection = $this->poolConfig['pool']['init-connection'];
        $this->freeConnection = new ObjectArray();
        $this->activeConnection = new ObjectArray();
        for ($i = 0; $i < $initConnection; $i++) {
            //todo 创建链接,存入数组
            $this->createConnect();
        }
    }

    private function createConnect()
    {
        $connection = $this->factory->create();
        if ($connection->getIsAsync()) {
            $this->activeConnection->push($connection);
        } else {
            $this->freeConnection->push($connection);
        }

        $connection->setPool($this);
        $connection->heartbeat();
        $connection->setEngine($this->type);
    }

    public function getFreeConnection()
    {
        return $this->freeConnection;
    }

    public function getActiveConnection()
    {
        return $this->activeConnection;
    }


    public function reload(array $config)
    {
    }

    public function get()
    {
        if ($this->freeConnection->isEmpty()) {
            return null;
        }
        $conn = $this->freeConnection->pop();
        $this->activeConnection->push($conn);
        $conn->lastUsedTime = Time::current(true);

//        deferRelease($conn);
        return $conn;
    }

    public function recycle(Connection $conn)
    {
        $evtName = null;
        if ($this->freeConnection->isEmpty()) {
            $evtName = $this->poolConfig['pool']['pool_name'] . '_free';
        }
        
        $this->freeConnection->push($conn);
        $this->activeConnection->remove($conn);
        if (count($this->freeConnection) == 1) {
            $evtName = $this->poolConfig['pool']['pool_name'] . '_free';
            Event::fire($evtName, [], false);
        }
    }

    public function remove(Connection $conn)
    {
        $this->activeConnection->remove($conn);
        $this->createConnect();
    }

}
