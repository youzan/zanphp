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
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\Types\ObjectArray;
use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Foundation\Coroutine\Task;

class Pool implements ConnectionPool
{
    private $freeConnection = null;

    private $activeConnection = null;

    private $poolConfig = null;

    private $factory = null;

    private $type = null;

    public $waitNum = 0;

    private $reconnectTime=[];

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
        $min = $this->poolConfig['pool']['minimum-connection-count'];
        if ($initConnection < $min) {
            $initConnection = $min;
        }
        $this->freeConnection = new ObjectArray();
        $this->activeConnection = new ObjectArray();
        for ($i = 0; $i < $initConnection; $i++) {
            //todo 创建链接,存入数组
            $this->createConnect();
        }
    }

    private function createConnect($connKey=null)
    {
        $max = isset($this->poolConfig['pool']['maximum-connection-count']) ?
            $this->poolConfig['pool']['maximum-connection-count'] : 30;
        $sumCount = $this->activeConnection->length() + $this->freeConnection->length();
        if($sumCount >= $max) {
            return null;
        }
        $connection = $this->factory->create();

        if ($connKey != null && $this->type == 'Mysqli') {
            if (!$connection->getSocket()->connect_errno){
                unset($this->reconnectTime[$connKey]);
            } else {
                $this->remove($connection);
            }
        }
        unset($this->reconnectTime[$connKey]);

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

    public function get($connection = null)
    {
        if ($this->freeConnection->isEmpty()) {
            yield $this->createConnect();
        }

        if (null == $connection) {
            $connection = $this->freeConnection->pop();
            $this->activeConnection->push($connection);
        } else {
            $this->freeConnection->remove($connection);
            $this->activeConnection->push($connection);
        }

        $connection->setUnReleased();
        $connection->lastUsedTime = Time::current(true);
        yield $this->insertActiveConnectionIntoContext($connection);
        yield $connection;
    }

    public function recycle(Connection $conn)
    {
        $evtName = null;
        
        $this->freeConnection->push($conn);
        $this->activeConnection->remove($conn);

        $coroutine = $this->deleteActiveConnectionFromContext($conn);
        Task::execute($coroutine);

        if (count($this->freeConnection) == 1) {
            $evtName = $this->poolConfig['pool']['pool_name'] . '_free';
            Event::fire($evtName, [], false);
            $this->waitNum = $this->waitNum >0 ? $this->waitNum-- : 0 ;
        }
    }

    public function remove(Connection $conn)
    {
        $coroutine = $this->deleteActiveConnectionFromContext($conn);
        Task::execute($coroutine);
        $this->activeConnection->remove($conn);

        $connHashCode = spl_object_hash($conn);
        if (!isset($this->reconnectTime[$connHashCode])) {
            $this->reconnectTime[$connHashCode] = 0;
            $this->createConnect($connHashCode);
        }

        ReconnectionPloy::getInstance()->reconnect($conn, $this);
    }

    private function insertActiveConnectionIntoContext($connection)
    {
        $activeConnections = (yield getContext($this->getActiveConnectionContextKey(), []));
        $activeConnections[spl_object_hash($connection)] = $connection;
        yield setContext($this->getActiveConnectionContextKey(), $activeConnections);
    }

    private function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext($this->getActiveConnectionContextKey(), []));
        if (isset($activeConnections[spl_object_hash($connection)])) {
            unset($activeConnections[spl_object_hash($connection)]);
        }
        return;
    }

    private function getActiveConnectionContextKey()
    {
        return $this->type . '_active_connections';
    }

    public function getActiveConnectionsFromContext()
    {
        yield getContext($this->getActiveConnectionContextKey(), []);
    }
}
