<?php

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Condition;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ConditionException;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\Exception\CanNotCreateConnectionException;
use Zan\Framework\Network\Connection\Exception\ConnectTimeoutException;
use Zan\Framework\Network\Connection\Exception\GetConnectionTimeoutFromPool;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class ConnectionManager
{

    use Singleton;

    /**
     * @var Pool[]
     */
    private static $poolMap = [];

    /**
     * @var PoolEx[]
     */
    private static $poolExMap = [];

    private static $server;

    public static $getPoolEvent = "getPoolEvent";

    /**
     * @param string $connKey
     * @return \Zan\Framework\Contract\Network\Connection
     * @throws InvalidArgumentException | CanNotCreateConnectionException | ConnectTimeoutException
     */
    public function get($connKey)
    {
        while (!isset(self::$poolMap[$connKey]) && !isset(self::$poolExMap[$connKey])) {
            try {
                yield new Condition(static::$getPoolEvent, 300);
            } catch (ConditionException $e) {
                sys_error($e->getMessage());
            }
        }

        if (isset(self::$poolExMap[$connKey])) {
            yield $this->getFromPoolEx($connKey);
        } else if(isset(self::$poolMap[$connKey])){
            yield $this->getFromPool($connKey);
        } else {
            throw new InvalidArgumentException('No such ConnectionPool:'. $connKey);
        }
    }

    private function getFromPool($connKey)
    {
        $pool = self::$poolMap[$connKey];

        $conf = $pool->getPoolConfig();
        $maxWaitNum = $conf['pool']['maximum-wait-connection'];
        if ($pool->waitNum > $maxWaitNum) {
            throw new CanNotCreateConnectionException("Connection $connKey has up to the maximum waiting connection number");
        }

        $connection = (yield $pool->get());

        if ($connection instanceof Connection) {
            yield $connection;
        } else {
            yield new FutureConnection($this, $connKey, $conf["connect_timeout"], $pool);
        }
    }

    private function getFromPoolEx($connKey)
    {
        $pool = self::$poolExMap[$connKey];
        $connection = (yield $pool->get());

        if ($connection instanceof Connection) {
            yield $connection;
        } else {
            throw new GetConnectionTimeoutFromPool("get connection $connKey timeout");
        }
    }

    /**
     * @param $poolKey
     * @param Pool|PoolEx $pool
     * @throws InvalidArgumentException
     */
    public function addPool($poolKey, $pool)
    {
        if ($pool instanceof Pool) {
            self::$poolMap[$poolKey] = $pool;
        } else if ($pool instanceof PoolEx) {
            self::$poolExMap[$poolKey] = $pool;
        } else {
            throw new InvalidArgumentException("invalid pool type, poolKey=$poolKey");
        }
    }

    public function monitor()
    {
        Timer::tick(30 * 1000, function() {
            foreach (self::$poolExMap as $poolKey => $pool) {
                $info = $pool->getStatInfo();
                $all = $info["all"];
                $free = $info["free"];
                sys_echo("pool_ex info [type=$poolKey, all=$all, free=$free]");
            };
        });
    }

    public function setServer($server)
    {
        self::$server = $server;
    }

    public function monitorConnectionNum()
    {
        MonitorConnectionNum::getInstance()->controlLinkNum(self::$poolMap);
    }
}
