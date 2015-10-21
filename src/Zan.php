<?php
namespace Zan\Framework;

if(defined('ZANPHP')){
    return ;
}
define('ZANPHP','youzan');

require __DIR__ . '/../vendor/autoload.php';

class Zan {

    public static function createHttpAppliction($config) {
        
    }

    public static function createSocketApplication($config) {
        
    }

    public static function init() {
        self::initAutoLoad();
        self::initClassAlias();
    }

    private static function initAutoLoad() {

    }

    private static function initClassAlias() {
        $classAliasMap = require __DIR__ . '/ClassAlias.php';

        if(!$classAliasMap) return true;

        foreach($classAliasMap as $alias => $original) {
            class_alias($original,$alias);
        }
    }

}

Zan::init();
