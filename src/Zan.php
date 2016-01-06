<?php

namespace Zan\Framework;

use Zan\Framework\Foundation\Exception\Handler;

defined('ZANPHP') or define('ZANPHP', 'youzan');
defined('RESOURCE_PATH') or define('RESOURCE_PATH', APP_PATH.'/resource');
defined('CONFIG_PATH') or define('CONFIG_PATH', RESOURCE_PATH.'/config');
defined('FILTER_PATH') or define('FILTER_PATH', APP_PATH.'/init');

require (__DIR__ . '/../vendor/autoload.php');

class Zan {

    public static function createHttpApplication($config)
    {
        return new \HttpApplication($config);
    }

    public static function createSocketApplication()
    {

    }

    public static function init()
    {
        self::initClassAlias();
        self::initErrorHandler();
    }

    private static function initErrorHandler()
    {
        Handler::initErrorHandler();
    }

    private static function initClassAlias()
    {
        $classAliasMap = require (__DIR__.'/ClassAlias.php');
        if (!$classAliasMap) return true;

        foreach($classAliasMap as $alias => $original) {
            class_alias($original, $alias);
        }
    }
}
Zan::init();
