<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/6/24
 * Time: 上午9:56
 */

namespace Zan\Framework\Foundation\Core;


class IronConfig
{

    private static $data = [];

    private static $fileCache = [];

    private static function get($key,$default=null)
    {
        $keys = explode('.',$key);
        $map  = & self::$data;
        do{
            $key = array_shift($keys);
            if(!isset($map[$key])){
                self::loadConfig($key);
            }
            if(!isset($map[$key])){
                return $default;
            }
            $map = &$map[$key];
            $runMode = self::getRunMode($map);
            if(isset($map[$runMode]) ){
                $map = &$map[$runMode];
            }

        }while(!empty($keys));

        return $map;
    }



    private static function loadConfig($fileName)
    {
        $configPath = Path::getIronPath();
        $config = null;
        $file   = $configPath . $fileName . '.php';
        if(isset(self::$fileCache[$fileName])){
            $config = self::$fileCache[$fileName];
        }elseif(is_file($file)){
            $config  = require_once $file;
            self::$fileCache[$fileName] = $config;
        }
        $runMode = self::getRunMode($config);
        if(isset($config[$runMode])){
            $config = $config[$runMode];
        }
        self::$data[$fileName] = $config;
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

}