<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/6/24
 * Time: 上午9:56
 */

namespace Zan\Framework\Foundation\Core;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Utilities\Types\Arr;

class IronConfig
{

    private static $configMap = [];

    public static function init()
    {
        $whiteList = Config::get('iron_config.files');
        if(empty($whiteList)){
            return;
        }
        self::$configMap = self::load($whiteList);
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


    private static  function getRunMode($config){
        $runMode   = RunMode::get();
        if(empty($config)){
            return $runMode;
        }
        if(isset($config[$runMode]) ){
            return $runMode;
        }elseif('unittest' == $runMode && isset($config['test'])){
            $runMode = 'test';
        }elseif('pre' == $runMode && !isset($config['pre']) && isset($config['online'])){
            $runMode = 'online';
        }
        return $runMode;
    }


    private static function load(array $files,$ignoreStructure = false)
    {


        $path = Dir::formatPath(Path::getIronPath());
        $configMap = [];
        foreach($files as $file){
            $configFile = $path.$file;
            $loadedConfig = require $configFile;
            if(!is_array($loadedConfig)){
                throw new InvalidArgumentException("syntax error find in config file: " . $configFile);
            }
            $runMode = self::getRunMode($loadedConfig);
            if(isset($loadedConfig[$runMode])) {
                $loadedConfig = $loadedConfig[$runMode];
            }
            if(!$ignoreStructure){
                $keyString = substr($configFile, strlen($path), -4);
                $loadedConfig = Arr::createTreeByList(explode('/',$keyString),$loadedConfig);
            }
            $configMap = Arr::merge($configMap,$loadedConfig);
        }

        return $configMap;
    }

}