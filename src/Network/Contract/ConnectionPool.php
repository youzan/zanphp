<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/14
 * Time: 22:22
 */

namespace Zan\Framework\Network\Contract;


use Zan\Framework\Foundation\Contract\PooledObject;
use Zan\Framework\Network\Contract\ConnectionPool as Pool;

class ConnectionPool {
    private static $poolMap = [];

    public static function init()
    {
        self::$poolMap = [];
    }

    public static function registerPool($key, Pool $pool)
    {
        self::$poolMap[$key] = $pool;
    }

    public static function get($key)
    {
        if(!isset(self::$poolMap[$key])) {
            return null;
        }
    }

    public static function release(PooledObject $obj)
    {

    }
}