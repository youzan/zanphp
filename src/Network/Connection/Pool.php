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
    /**
     * @var ObjectArray
     */
    private $freeConnection = null;

    /**
     * @var ObjectArray
     */
    private $activeConnection = null;

    private $poolConfig = null;

    private $factory = null;

    private $type = null;

    public $waitNum = 0;

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
        $min = isset($this->poolConfig['pool']['minimum-connection-count']) ?
            $this->poolConfig['pool']['minimum-connection-count'] : 2;
        if ($initConnection < $min) {
            $initConnection = $min;
        }
        $this->freeConnection = new ObjectArray();
        $this->activeConnection = new ObjectArray();
        for ($i = 0; $i < $initConnection; $i++) {
            $this->createConnect();
        }
    }

    public function createConnect($previousConnectionHash = '', $prevConn = null)
    {
        $max = isset($this->poolConfig['pool']['maximum-connection-count']) ?
            $this->poolConfig['pool']['maximum-connection-count'] : 30;
        $sumCount = $this->activeConnection->length() + $this->freeConnection->length();
        if($sumCount >= $max) {
            return null;
        }
        $connection = $this->factory->create();
        if (null === $connection)
            return;
        $isSwoole2x = \swoole2x();
        if  ('' !== $previousConnectionHash) {
            $previousKey = ReconnectionPloy::getInstance()->getReconnectTime($previousConnectionHash);
            if ($this->type == 'Mysqli') {

                $errno = 0;
                if ($isSwoole2x && null !== $prevConn) {
                    $sock = $prevConn->getSocket();
                    if (_mysql2()) {
                        $errno = $sock->connect_errno;
                    } else {
                        $errno = $sock->errno;
                    }

                } else if (!$isSwoole2x) {
                    $errno = $connection->getSocket()->connect_errno;
                }

                if ($isSwoole2x && $errno) {
                    ReconnectionPloy::getInstance()->setReconnectTime(spl_object_hash($connection),$previousKey);
                    $this->freeConnection->remove($prevConn);
                    $this->activeConnection->remove($prevConn);
                } else if (!$isSwoole2x) {
                    if ($errno) {
                        ReconnectionPloy::getInstance()->setReconnectTime(spl_object_hash($connection), $previousKey);
                        $this->remove($connection);
                    } else {
                        $connection->heartbeat();
                    }
                }

                $connection->setPool($this);
            } else {
                ReconnectionPloy::getInstance()->setReconnectTime(spl_object_hash($connection), $previousKey);
            }
            ReconnectionPloy::getInstance()->connectSuccess($previousConnectionHash);
        }

        if ($connection->getIsAsync()) {
            $this->activeConnection->push($connection);
        } else {
            $this->freeConnection->push($connection);
        }
        if ('' == $previousConnectionHash) {
            if ($this->type !== 'Mysqli' || ($this->type === 'Mysqli' && !$isSwoole2x)) {
                $connection->heartbeat();
            }
        }
        $connection->setPool($this);
        $connection->setEngine($this->type);
    }

    /**
     * @return ObjectArray
     */
    public function getFreeConnection()
    {
        return $this->freeConnection;
    }

    /**
     * @return ObjectArray
     */
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
            $this->createConnect();
        }

        if (null == $connection) {
            $connection = $this->freeConnection->pop();
            if (null != $connection) {
                $this->activeConnection->push($connection);
            }

        } else {
            $this->freeConnection->remove($connection);
            $this->activeConnection->push($connection);
        }
        if (null === $connection) {
            yield null;
            return;
        }
        $connection->setUnReleased();
        $connection->lastUsedTime = Time::current(true);
        yield $connection;
    }

    public function recycle(Connection $conn)
    {
        $evtName = null;
        
        $this->freeConnection->push($conn);
        $this->activeConnection->remove($conn);

        $evtName = $this->poolConfig['pool']['pool_name'] . '_free';
        Event::fire($evtName, [], false);
    }

    public function remove(Connection $conn)
    {
        $this->freeConnection->remove($conn);
        $this->activeConnection->remove($conn);
        $connHashCode = spl_object_hash($conn);
        if (null === ReconnectionPloy::getInstance()->getReconnectTime($connHashCode)) {
            ReconnectionPloy::getInstance()->setReconnectTime($connHashCode, 0);
            $this->createConnect($connHashCode, $conn);
            return;
        }

        ReconnectionPloy::getInstance()->reconnect($conn, $this);
    }

    /**
     * @return array|null
     */
    public function getPoolConfig()
    {
        return $this->poolConfig;
    }
}
