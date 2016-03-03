<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\Types\Dir;

class Config
{
    private static $configMap = [];

    public static function init()
    {
    }

    public static function reload()
    {
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