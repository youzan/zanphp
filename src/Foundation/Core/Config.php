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
        //add private dir
        if (RunMode::get() == 'test') {
            $privatePath = Path::getConfigPath() . '.private/';
            if (is_dir($privatePath)) {
                $privateConfig = ConfigLoader::getInstance()->load($privatePath);
                self::$configMap = Arr::merge(self::$configMap, $privateConfig);
            }
        }
    }

    public static function get($key, $default = null)
    {
        $preKey = $key;
        $routes = explode('.',$key);
        if(empty($routes)){
            return $default;
        }

        $result = &self::$configMap;
        $hasConfig = true;
        foreach($routes as $route){
            if(!isset($result[$route])){
                $hasConfig = false;
                break;
            }
            $result = &$result[$route];
        }
        if(!$hasConfig){
            $result = IronConfig::get($preKey,$default);
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