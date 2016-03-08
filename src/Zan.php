<?php
namespace Zan\Framework;

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

