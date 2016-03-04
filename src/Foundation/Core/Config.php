<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Test\Foundation\Core\ConfigLoaderTest;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class Config
{
    private static $configMap = [];

    public static function init()
    {
        $path = Path::getConfigPath();
        $sharePath = $path. 'share/';
        $shareConfigMap = ConfigLoader::getInstance()->load($sharePath);
        $otherPath = Path::getConfigPath().RunMode::get();
        $config = ConfigLoader::getInstance()->load($otherPath);
        self::$configMap = Arr::merge(self::$configMap,$shareConfigMap,$config);
    }


    public static function reload()
    {
        self::clear();
        self::init();
    }

    public static function get($key, $default = null)
    {
        $routes = explode('.',$key);
        if(empty($routes)){
            return $default;
        }
        $result = self::$configMap;
        foreach($routes as $route){
            if(!isset($result[$route])){
                $result = null;
                break;
            }
            $result = $result[$route];
        }
        if(null == $result){
            $result = $default;
        }
        return $result;
    }

    public static function set($key, $value)
    {
        $routes = explode('.',$key);
        if(empty($routes) || empty($value)){
            return false;
        }
        $newConfigMap = Arr::createMap($routes,$value);
        if(empty($newConfigMap)){
            return false;
        }
        self::$configMap = Arr::merge(self::$configMap,$newConfigMap);
        return true;
    }

    public static function clear()
    {
        self::$configMap = [];
    }
}