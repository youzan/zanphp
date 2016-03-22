<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:23
 */

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Client\FutureConnection;
use Zan\Framework\Network\Common\ConnectionPool as Pool;


class ConnectionManager {

    private static $_config=null;
    private static $poolMap = [];
    private static $_registry=[];

    public function __construct($config) {
        //self::$_config = $config;
        self::configDemo();
        //$this->init();
    }

    public function init()
    {
        $connectionPool = new Pool(self::$_config);
        $key = self::$_config['pool_name'];
        self::$_registry[] = $key;//注册连接池
        self::$poolMap[$key] = $connectionPool;
    }


    public static function get($key) /* Connection */
    {
        if(!isset(self::$poolMap[$key])){
            yield null;
        }
        $pool = self::$poolMap[$key];
        $conn = $pool->get();
        if ($conn) {
            yield $conn;
        }

        ;
        $conn = (yield new FutureConnection($key));;
        deferRelease($conn);
    }

    public static function release($key=null,Connection $conn)
    {
        self::$poolMap[$key]->release($conn);
    }

    public static function configDemo() {
        self::$_config['host']= '192.168.66.202:3306';
        self::$_config['user'] = 'test_koudaitong';
        self::$_config['pool_name'] = 'p_zan';
        self::$_config['maximum-connection-count'] ='100';
        self::$_config['minimum-connection-count'] = '10';
        self::$_config['keeping-sleep-time'] = '10';//等待时间
        self::$_config['maximum-new-connections'] = '5';
        self::$_config['prototype-count'] = '5';
        self::$_config['init-connection'] = '10';
    }

}


