<?php
namespace Zan\Framework;

use Zan\Framework\Network\Http\Application as HttpApplication;
use Zan\Framework\Network\Tcp\Application as TcpApplication;

class Zan {

    public static function createHttpApplication($config)
    {
        self::init();

        return new HttpApplication($config);
    }

    public static function createTcpApplication($config)
    {
        self::init();

        return new TcpApplication($config);
    }

    public static function createApplication($config)
    {
        self::init();
    }

    public static function init()
    {
        self::initClassAlias();
    }

    private static function initClassAlias()
    {
        if(!file_exists(__DIR__ . '/ClassAlias.php')){
            return null;
        }

        require __DIR__ . '/ClassAlias.php';

        if (isset($classAliasMap) && $classAliasMap ){
            self::initClassAliasMap($classAliasMap);
        }

        if (isset($classAliasPathes) && $classAliasPathes ){
            self::initClassAliasPathes($classAliasPathes);
        }
    }

    private static function initClassAliasMap($classAliasMap)
    {
        foreach($classAliasMap as $alias => $original) {
            class_alias($original, $alias);
        }
    }

    private static function initClassAliasPathes($classAliasPathes)
    {

    }
}

