<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\Types\Dir;

class Config
{
    private static $configMap = [];
    private static $inited = false;

    public static function init()
    {
        if (self::$inited) return true;

        self::$inited = true;
    }

    public static function isInited()
    {
        return self::$inited;
    }


    public static function env($key)
    {
        return get_cfg_var('kdt.' . $key);
    }

    public static function get($key, $default = null)
    {

    }

    public static function set($key, $value)
    {
        self::$configMap[$key] = $value;
    }

    public static function clear()
    {
        self::$configMap = [];
    }

}