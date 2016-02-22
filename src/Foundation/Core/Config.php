<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\Types\Dir;

class Config
{
    private static $configPath = '';
    private static $configMap = [];
    private static $inited = false;

    public static function init()
    {
        if (self::$inited) return true;

        self::$inited = true;
        self::clear();
        self::setRunMode();
    }

    public static function isInited()
    {
        return self::$inited;
    }

    private static function setRunMode()
    {
        switch (self::env('RUN_MODE')) {
            case 'test':
                self::set('run_mode','test');
                break;
            case 'unittest':
                self::set('run_mode','unittest');
                break;
            case 'readonly':
                self::set('run_mode','readonly');
                break;
            default:
                self::set('run_mode','online');
                break;
        }
        self::set('debug', Config::env('DEBUG') ? true : false);
    }

    public static function setConfigPath($path)
    {
        if(!$path || !is_dir($path)) {
            throw new InvalidArgument('invalid path for Config ' . $path);
        }
        self::$configPath = Dir::formatPath($path);
    }

    public static function env($key)
    {
        return get_cfg_var('kdt.'.$key);
    }

    public static function get($key, $default=null)
    {
        $keys = explode('.',$key);
        $map  = & self::$configMap;
        do{
            $key = array_shift($keys);
            if(!isset($map[$key])){
                self::getConfigFile($key);
            }
            if(!isset($map[$key])){
                return $default;
            }
            $map = &$map[$key];

            $run_mode   = self::$configMap['run_mode'];
            if(isset($map[$run_mode]) ){
                $map = &$map[$run_mode];
            }elseif('unittest' == $run_mode && isset($map['test'])){
                $map = &$map['test'];
            }elseif (('readonly' == $run_mode) && isset($map['online'])){
                $map = &$map['online'];
            }elseif (('pre' == $run_mode) && !isset($map['pre']) && isset($map['online'])){
                $map = &$map['online'];
            }
        }while(!empty($keys));

        return $map;
    }

    public static function set($key,$value)
    {
        self::$configMap[$key]   = $value;
    }

    public static function clear()
    {
        self::$configMap = [];
    }

    private static function getConfigFile($key)
    {
        $envRunMode = self::$configMap['run_mode'] == 'online' ? 'online' : 'test';
        $configFile = self::$configPath . $envRunMode.'/'. $key . '.php';
        $commonConfigFile = self::$configPath . 'common'.'/'. $key . '.php';

        if (!($isExistsEnv = file_exists($configFile)) && !($isExistsCommon = file_exists($commonConfigFile))) {
            throw new InvalidArgument('No such config file ' . $configFile);
        }
        $envConfig = [];
        if ($isExistsEnv) {
            $envConfig = require $configFile;
        }
        $commonConfig = [];
        if ($isExistsCommon) {
            $commonConfig = require $commonConfigFile;
        }
        return self::$configMap[$key] = array_merge($envConfig, $commonConfig);
    }
}