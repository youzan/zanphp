<?php

namespace Zan\Framework;

if(defined('ZAN_INIT')){
    return null;
}
define('ZAN_INIT','zan_inited');

require (__DIR__ . '/../vendor/autoload.php');

class Zan {

    public static function createHttpApplication($config)
    {
        return new \HttpApplication($config);
    }

    public static function createTcpApplication($config)
    {
        return new \TcpApplication($config);
    }

    public static function createSocketApplication()
    {

    }

    public static function init()
    {
        self::initClassAlias();
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
