<?php
namespace Zan\Framework;

//use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\Handler;

defined('ZANPHP') or define('ZANPHP', 'youzan');
defined('RESOURCE_PATH') or define('RESOURCE_PATH', APP_PATH.'/resource');
defined('CONFIG_PATH') or define('CONFIG_PATH', RESOURCE_PATH.'/config');

require (__DIR__ . '/../vendor/autoload.php');

class Zan {

    public static function createHttpApplication($config) {
        
    }

    public static function createSocketApplication($config) {
        
    }

    public static function init() {
        var_dump(class_exists(''));exit;
       // new AAAA();exit;
        self::initClassAlias();
        self::initConfig();
        self::initErrorHandler();
    }

    private static function initConfig() {
        Config::init();
        Config::setConfigPath(CONFIG_PATH);
    }

    private static function initErrorHandler() {
        Handler::initErrorHandler();
    }

    private static function initClassAlias() {
        $classAliasMap = require (__DIR__.'/ClassAlias.php');
        if (!$classAliasMap) return true;
        foreach($classAliasMap as $alias => $original) {
            class_alias($original, $alias);
        }
    }
}
Zan::init();
