<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\ConnectionFactory;
use Zan\Framework\Contract\Network\ConnectionPool;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Event;
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
        $initConnection = $this->poolConfig['pool']['init-connection'];
        $this->freeConnection = new ObjectArray();
        $this->activeConnection = new ObjectArray();
        for ($i=0; $i<$initConnection; $i++) {
            //todo 创建链接,存入数组
            $this->createConnect();
        }

    }

    private function createConnect()
    {
        //todo 创建链接,存入数组
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

//        deferRelease($conn);
        return $conn;
    }
    
    public function recycle(Connection $conn)
    {
        $this->freeConnection->push($conn);
        $this->activeConnection->remove($conn);
        if (count($this->freeConnection) == 1) {
            //唤醒等待事件
            $evtName = $this->poolConfig['pool']['pool_name'] . '_free';
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