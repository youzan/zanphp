<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:23
 */

namespace Zan\Framework\Network\Facade;

use Zan\Framework\Network\Contract\ConnectionPool as Pool;


class ConnectionFacade {
    private static $poolMap = [];

    public static function init()
    {
        self::$poolMap = [];
    }

    public static function addPool($key, Pool $pool)
    {
        self::$poolMap[$key] = $pool;
    }

    public static function get($key) /* Connection */
    {
        if(!isset(self::$poolMap[$key])){
            return null;
        }
        $pool = self::$poolMap[$key];
        $conn = (yield $pool->get());

        defer(function() use($conn, $key){
            ConnectionManager::release($conn, $key);
        });
    }

    public static function release($key=null,Connection $conn)
    {

    }

}


