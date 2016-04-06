<?php
/**
 * @author hupp
 * create date: 16/03/10
 */
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class UrlRule {

    private static $rules = [];

    public static function loadRules($routingPath)
    {
        $routeFiles = Dir::glob($routingPath, '*.php');

        if (!$routeFiles) return false;

        foreach ($routeFiles as $file)
        {
            $route = include $file;
            if (!is_array($route)) continue;
            self::$rules = Arr::merge(self::$rules, self::optimizeRules($route));
        }
    }

    public static function getRules()
    {
        return self::$rules;
    }

    private static function optimizeRules(array $rules)
    {
        if(empty($rules)) {
            return [];
        }
        $controllerFullMatch = $actionFullMatch = $normalMatch = [];
        foreach($rules as $key => $value) {
            $hasRegex = false;
            $regex = $key;
            $tree = array_filter(explode('/', $regex));
            if(empty($tree)) continue;
            foreach($tree as $leafKey => $leaf) {
                if('.*' === trim($leaf)) {
                    if(2 == (int)$leafKey) {
                        $controllerFullMatch[$key] = $value;
                    } elseif(3 == (int)$leafKey) {
                        $actionFullMatch[$key] = $value;
                    }
                    $hasRegex = true;
                    break;
                }
            }
            if(!$hasRegex) {
                $normalMatch[$key] = $value;
            }
        }
        return array_merge($normalMatch, $actionFullMatch, $controllerFullMatch);
    }
}