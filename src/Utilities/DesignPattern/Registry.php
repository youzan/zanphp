<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:35
 */

namespace Zan\Framework\Utilities\DesignPattern;


class Registry  {
    private static $classMap = [];

    public static function get($key, $default=null)
    {
        if(!isset(self::$classMap[$key])) {
            return $default;
        }

        return self::$classMap[$key];
    }

    public static function set($key, $value)
    {
        self::$classMap[$key] = $value;
    }

    public static function contain($key)
    {
        return isset(self::$classMap[$key]);
    }
}