<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/1
 * Time: 18:12
 */

namespace Zan\Framework\Network\Connection;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
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

    public function setServer($server) {
        self::$server = $server;
    }
}
