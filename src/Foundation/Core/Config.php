<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Utilities\Types\Arr;

class Config
{
    private static $configMap = [];

    public static function init()
    {
        $path = Path::getConfigPath();
        $sharePath = $path . 'share/';
        $shareConfigMap = ConfigLoader::getInstance()->load($sharePath);

        $runModeConfigPath = Path::getConfigPath() . RunMode::get();
        $runModeConfig = ConfigLoader::getInstance()->load($runModeConfigPath);

        self::$configMap = Arr::merge(self::$configMap,$shareConfigMap,$runModeConfig);
    }

    public static function get($key, $default = null)
    {
        $routes = explode('.',$key);
        if(empty($routes)){
            return $default;
        }

        $result = &self::$configMap;
        foreach($routes as $route){
            if(!isset($result[$route])){
                return $default;
            }

            $result = &$result[$route];
        }

        return $result;
    }

    public static function set($key, $value)
    {
        $routes = explode('.',$key);
        if(empty($routes)){
            return false;
        }

        $newConfigMap = Arr::createTreeByList($routes,$value);
        self::$configMap = Arr::merge(self::$configMap,$newConfigMap);

        return true;
    }

    public static function clear()
    {
        self::$configMap = [];
    }
}