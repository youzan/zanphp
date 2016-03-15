<?php
/**
 * @author hupp
 * create date: 16/03/10
 */
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class UrlRule {

    const ROUTE_KEY = 'rewrite';

    private static $rules = [];

    public static function loadRules($routingPath)
    {
        $routeFiles = Dir::glob($routingPath, '*.php');

        if (!$routeFiles) return false;

        foreach ($routeFiles as $file)
        {
            $route = include $file;

            if (!isset($route[self::ROUTE_KEY]))  continue;
            if (!is_array($route[self::ROUTE_KEY])) continue;

            self::$rules = Arr::merge(self::$rules, $route[self::ROUTE_KEY]);
        }
    }

    public static function getRules()
    {
        return self::$rules;
    }

}