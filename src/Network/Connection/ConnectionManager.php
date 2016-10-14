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
use Zan\Framework\Network\Connection\Exception\CanNotCreateConnectionException;
use Zan\Framework\Network\Connection\Exception\ConnectTimeoutException;
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
     * @throws InvalidArgumentException | CanNotCreateConnectionException | ConnectTimeoutException
     */
    public function get($connKey, $timeout=0)
    {
        if(!isset(self::$poolMap[$connKey])){
            throw new InvalidArgumentException('No such ConnectionPool:'. $connKey);
        }
        /* @var $pool Pool */
        $pool = self::$poolMap[$connKey];

        $poolConf = $pool->getPoolConfig();
        $maxWaitNum = $poolConf['pool']['maximum-wait-connection'];
        if ($pool->waitNum > $maxWaitNum) {
            throw new CanNotCreateConnectionException("Connection $connKey has up to the maximum waiting connection number");
        }

        $connection = (yield $pool->get());
        if ($connection) {
            yield $connection;
            return;
        }

        $pool->waitNum++;
        yield new FutureConnection($this, $connKey, $poolConf['connect_timeout'], $pool);
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
                    'worker_id' => (string)self::$server->swooleServer->worker_id
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

    public function monitorConnectionNum()
    {
        MonitorConnectionNum::getInstance()->controlLinkNum(self::$poolMap);
    }
}
