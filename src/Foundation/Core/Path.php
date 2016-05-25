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
use Zan\Framework\Foundation\Core\Config;

class Path {
    const DEFAULT_CONFIG_PATH   = 'resource/config/';
    const DEFAULT_SQL_PATH      = 'resource/sql/';
    const DEFAULT_LOG_PATH      = 'resource/log/';
    const DEFAULT_CACHE_PATH    = 'resource/cache/';
    const DEFAULT_MODEL_PATH    = 'resource/model/';
    const DEFAULT_TABLE_PATH    = 'resource/config/share/table/';
    const DEFAULT_ROUTING_PATH  = 'init/routing';

    const ROOT_PATH_CONFIG_KEY    = 'path.root';
    const CONFIG_PATH_CONFIG_KEY  = 'path.config';
    const SQL_PATH_CONFIG_KEY     = 'path.sql';
    const LOG_PATH_CONFIG_KEY     = 'path.log';
    const CACHE_PATH_CONFIG_KEY   = 'path.cache';
    const MODEL_PATH_CONFIG_KEY   = 'path.model';
    const TABLE_PATH_CONFIG_KEY   = 'path.table';
    const ROUTING_PATH_CONFIG_KEY = 'path.routing';

    private static $rootPath    = null;
    private static $configPath  = null;
    private static $sqlPath     = null;
    private static $logPath     = null;
    private static $cachePath   = null;
    private static $modelPath   = null;
    private static $tablePath   = null;
    private static $routingPath = null;

    public static function init($rootPath)
    {
        self::setRootPath($rootPath);
        self::setOtherPathes();
        self::setInConfig();
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

    public static function getTablePath()
    {
        return self::$tablePath;
    }

    public static function getRoutingPath()
    {
        return self::$routingPath;
    }

    private static function setRootPath($rootPath)
    {
        self::$rootPath = Dir::formatPath($rootPath);
    }

    private static function setOtherPathes()
    {
        self::$configPath = self::$rootPath . self::DEFAULT_CONFIG_PATH;
        self::$sqlPath = self::$rootPath . self::DEFAULT_SQL_PATH;
        self::$logPath = self::$rootPath . self::DEFAULT_LOG_PATH;
        self::$modelPath = self::$rootPath . self::DEFAULT_MODEL_PATH;
        self::$cachePath = self::$rootPath . self::DEFAULT_CACHE_PATH;
        self::$tablePath = self::$rootPath . self::DEFAULT_TABLE_PATH;
        self::$routingPath = self::$rootPath . self::DEFAULT_ROUTING_PATH;
    }

    private static function setInConfig()
    {
        Config::set(self::ROOT_PATH_CONFIG_KEY, self::$rootPath);
        Config::set(self::CONFIG_PATH_CONFIG_KEY, self::$configPath);
        Config::set(self::SQL_PATH_CONFIG_KEY, self::$sqlPath);
        Config::set(self::LOG_PATH_CONFIG_KEY, self::$logPath);
        Config::set(self::CACHE_PATH_CONFIG_KEY, self::$cachePath);
        Config::set(self::MODEL_PATH_CONFIG_KEY, self::$modelPath);
        Config::set(self::TABLE_PATH_CONFIG_KEY, self::$tablePath);
        Config::set(self::ROUTING_PATH_CONFIG_KEY, self::$routingPath);
    }
}