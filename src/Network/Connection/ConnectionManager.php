<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/1
 * Time: 18:12
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\FutureConnection;
use Zan\Framework\Network\Connection\Factory\Mysqli;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class ConnectionManager
{

    use Singleton;

    private static $poolMap = [];
    private static $poolConfig=null;
    private static $mysqlConfig=null;
    private static $registry=[];

    public function __construct()
    {
    }
//
//    public function init()
//    {
//        $factory = new Mysqli(self::$mysqlConfig);
//        $connectionPool = new Pool($factory, self::$poolConfig);
//        $key = self::$poolConfig['pool_name'];
//        self::$registry[] = $key;//注册连接池
//        self::$poolMap[$key] = $connectionPool;
//        return $this;
//    }
    
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
     * @param ConnectionPool $pool
     */
    public function addPool($poolKey, Pool $pool)
    {
        self::$poolMap[$poolKey] = $pool;
    }


    public function configDemo() {
        self::$poolConfig['host']= '192.168.66.202:3306';
        self::$poolConfig['user'] = 'test_koudaitong';
        self::$poolConfig['pool_name'] = 'pifa';
        self::$poolConfig['maximum-connection-count'] ='100';
        self::$poolConfig['minimum-connection-count'] = '10';
        self::$poolConfig['keeping-sleep-time'] = '10';//等待时间
        self::$poolConfig['maximum-new-connections'] = '5';
        self::$poolConfig['prototype-count'] = '5';
        self::$poolConfig['init-connection'] = '10';
    }

    public function mysqlConfig()
    {
        self::$mysqlConfig['host'] = '192.168.66.202';
        self::$mysqlConfig['user'] = 'test_koudaitong';
        self::$mysqlConfig['password'] = 'nPMj9WWpZr4zNmjz';
        self::$mysqlConfig['database'] = 'pf';
        self::$mysqlConfig['port'] = '3306';
    }
}