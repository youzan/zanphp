<?php
/**
 * @author hupp
 * create date: 16/03/10
 */
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\Types\Dir;

class UrlRule {

    public static $default = [];
    public static $rules = [];

    public static function loadRules($routingPath)
    {
        //todo set default

        $routes = Dir::glob($routingPath, '*.php');

        //todo get routing
    }

    public static function getRules()
    {
        return self::$rules;
    }
}