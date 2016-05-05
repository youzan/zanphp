<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/1
 * Time: 18:12
 */

namespace Zan\Framework\Network\Connection;


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
    private static $poolConfig=null;
    private static $mysqlConfig=null;
    private static $registry=[];

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
        
        $connection = $pool->get();
        if($connection){
            yield $connection;
            return;
        }
        
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
        $time = Config::get('hawk.time');
        Timer::tick($time, [$this, 'monitorTick']);
    }

    public function monitorTick() {
        $hawk = new Hawk();

        foreach (self::$poolMap as $poolKey => $pool) {
            $activeNums = $pool->getActiveConnection()->length();
            $freeNums = $pool->getFreeConnection()->length();
            $total = $activeNums + $freeNums;

            $hawk->add(Constant::BIZ_CONNECTION_POOL, [
                    'total' => $total,
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
}