<?php
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Core\Config;

class UrlRule {

    use Singleton;

    private static $rules = [];

    public static function loadRules()
    {
        $routeFiles = Dir::glob(Config::get('path.routing'), '*.routing.php');

        if (!$routeFiles) return false;

        foreach ($routeFiles as $file)
        {
            $route = include $file;
            if (!is_array($route)) continue;
            self::$rules = Arr::merge(self::$rules, $route);
        }
    }

    public static function getRules()
    {
        return self::$rules;
    }
}