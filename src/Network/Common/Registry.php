<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:35
 */

namespace Zan\Framework\Network\Server;


class Registry  {
    private static $data = [];

    public static function get($key, $default=null)
    {
        if(!isset(self::$data[$key])) {
            return $default;
        }

        return self::$data[$key];
    }

    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }

    public static function contain($key)
    {
        return isset(self::$data[$key]);
    }
}