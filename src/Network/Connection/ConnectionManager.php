<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/1
 * Time: 18:12
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\FutureConnection;
use Zan\Framework\Network\Connection\Factory\Mysqli;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Monitor\Constant;
use Zan\Framework\Sdk\Monitor\Hawk;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class ConnectionManager
{

    use Singleton;

    private static $poolMap = [];

    private static $server;

    public function __construct()
    {
    }
    /**
     * @param string $connKey
     * @param int $timeout
     * @return \Zan\Framework\Contract\Network\Connection 
     * @throws InvalidArgumentException
     */
    public function get($connKey, $timeout=0)
    {
        if(!isset(self::$poolMap[$connKey])){
            throw new InvalidArgumentException('No such ConnectionPool:'. $connKey);
        }     
        $pool = self::$poolMap[$connKey];
        $connection = (yield $pool->get());
        if ($connection) {
            yield $connection;
            return;
        }
        $pool->waitNum++;
        yield new FutureConnection($this, $connKey, $timeout);
    }

    /**
     * @param $poolKey
     * @param Pool $pool
     */
    public function addPool($poolKey, Pool $pool)
    {
        self::$poolMap[$poolKey] = $pool;
    }

    public function monitor()
    {
        $config = Config::get('hawk');
        if (!$config['run']) {
            return;
        }
        $time = $config['time'];
        Timer::tick($time, [$this, 'monitorTick']);
    }

    public function monitorTick() {
        $hawk = new Hawk();

        foreach (self::$poolMap as $poolKey => $pool) {
            $activeNums = $pool->getActiveConnection()->length();
            $freeNums = $pool->getFreeConnection()->length();

            $hawk->add(Constant::BIZ_CONNECTION_POOL,
                [
                    'free'  => $freeNums,
                    'active' => $activeNums,
                ], [
                    'pool_name' => $poolKey,
                    'worker_id' => self::$server->swooleServer->worker_id
                ]
            );
        }

        $coroutine = $this->runHawkTask($hawk);
        Task::execute($coroutine);
    }

    public function runHawkTask($hawk) {
        yield $hawk->send();
    }

    public function setServer($server) {
        self::$server = $server;
    }

    public function closeConnectionByRequestTimeout()
    {
        foreach (self::$poolMap as $pool) {
            if ($pool instanceof Pool) {
                $connections = (yield $pool->getActiveConnectionsFromContext());
                if ([] == $connections) {
                    continue;
                }
                foreach ($connections as $connection) {
                    if (null != $connection && $connection instanceof Connection) {
                        $connection->close();
                    }
                }
            }
        }
    }

    public function controlLinkNum()
    {
        $config = Config::get('reconnection.base');
        $time = isset($config['interval-reduce-link'])?  $config['interval-reduce-link'] : 60000;
        Timer::tick($time, [$this, 'reduceLinkNum']);
    }

    public function reduceLinkNum()
    {
        $config = Config::get('reconnection.base');
        $reduceNum = isset($config['num-reduce-link']) ? $config['num-reduce-link'] : 1 ;
        foreach (self::$poolMap as $poolKey => $pool) {
            $activeNums = $pool->getActiveConnection()->length();
            $freeNums = $pool->getFreeConnection()->length();
            $sumNums = $activeNums + $freeNums;
            if ($sumNums <=0 || $freeNums*3 < $sumNums) {
                continue;
            }
            for ($i=0; $i<$reduceNum; $i++) {
                $conn = $pool->getFreeConnection()->pop();
                $conn->closeSocket();
            }
        }
    }

}
