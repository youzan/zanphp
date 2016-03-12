<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/9
 * Time: 00:46
 */

class Router {
    public static function addRouteMap($routeMap)
    {

    }

    public static function addRoute(Route $route, $path)
    {

    }
}

class Route {
    public function domain()
    {
        return $this;
    }

    public function path()
    {
        return $this;
    }

    public function method()
    {
        return $this;
    }

    public function parameter()
    {
        return $this;
    }
}

$routeMap = [
    'domain'    => [
        'shop{kdt_id}.youzan.com'       => '/shop'
    ],
    'rewrite'   => [

    ],
];

$route = new Route();
$route->domain()
    ->method()
    ->path()
    ->parameter();

Router::addRoute($route,'/xxxx');

