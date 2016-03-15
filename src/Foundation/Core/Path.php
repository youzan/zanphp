<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/2
 * Time: 17:18
 */

namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\Types\Dir;

class Path {
    const DEFAULT_CONFIG_PATH   = 'resource/config/';
    const DEFAULT_SQL_PATH      = 'resource/sql/';
    const DEFAULT_LOG_PATH      = 'resource/log/';
    const DEFAULT_CACHE_PATH    = 'resource/cache/';
    const DEFAULT_MODEL_PATH    = 'resource/model/';

    private static $rootPath    = null;
    private static $configPath  = null;
    private static $sqlPath     = null;
    private static $logPath     = null;
    private static $cachePath   = null;
    private static $modelPath   = null;

    public static function init($config)
    {
        self::setRootPath($config);
        self::setOtherPathes();
    }

    public static function getRootPath()
    {
        return self::$rootPath;
    }

    public static function getConfigPath()
    {
        return self::$configPath;
    }

    public static function setConfigPath($configPath){
        self::$configPath = $configPath;
    }

    public static function getSqlPath()
    {
        return self::$sqlPath;
    }

    public static function getLogPath()
    {
        return self::$logPath;
    }

    public static function getModelPath()
    {
        return self::$modelPath;
    }

    public static function getCachePath()
    {
        return self::$cachePath;
    }

    private static function setRootPath($config)
    {
        if(!isset($config['rootPath'])){
            throw new InvalidArgumentException('rootPath not defined in init.bootstrap file');
        }

        if (!is_dir($config['rootPath']) ) {
            throw new InvalidArgumentException('Application root path ({$dir}) is invalid!');
        }
        self::$rootPath = Dir::formatPath($config['rootPath']);
    }

    private static function setOtherPathes()
    {
        self::$configPath = self::$rootPath . self::DEFAULT_CONFIG_PATH;
        self::$sqlPath = self::$rootPath . self::DEFAULT_SQL_PATH;
        self::$logPath = self::$rootPath . self::DEFAULT_LOG_PATH;
        self::$modelPath = self::$rootPath . self::DEFAULT_MODEL_PATH;
        self::$cachePath = self::$rootPath . self::DEFAULT_CACHE_PATH;
    }

}