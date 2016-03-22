<?php
/**
 * @author hupp
 * create date: 16/01/15
 */
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Router {

    use Singleton;

    const BASIC_LEVEL = 3;

    private $config;
    private $url;
    private $rules  = [];
    private $routes = [];
    private $routeConKey = 'route';
    private $separator = '/';

    public function __construct($config = [])
    {
        $this->config = $config;

        if (!$this->config) {
            $this->config = Config::get($this->routeConKey);
        }
        $this->rules  = UrlRule::getRules();
    }

    public function route(Request $request)
    {
        $requestUri = $request->server->get('REQUEST_URI');
        $requestUri = ltrim($requestUri, '/');

        if (!$requestUri) {
            return $this->setDefaultRoute($request);
        }

        $request->setRoute($requestUri);
    }

    private function parseRegexRoute()
    {
        $rules = UrlRegex::formatRules($this->rules);
        $route = UrlRegex::decode($this->url, $rules);

        return [
            isset($route['url']) ? $route['url'] : $this->url,
            isset($route['parameter']) ? $route['parameter'] : [],
        ];
    }

    private function setRoutes($url)
    {
        $pathInfo = explode('/', ltrim($url, '/'));
        $pathInfo = array_filter($pathInfo);
        $levelLen = count($pathInfo);

        if ($levelLen >= self::BASIC_LEVEL) {
            $action = end($pathInfo);
            $pos    = strpos($action, '.');
            if ($pos === false) {
                $this->setAction($action);
                $this->setDefaultFormat();
            }else {
                $this->setAction(substr($action, 0, $pos));
                $this->setFormat(substr($action, $pos + 1));
            }
            $this->setController($pathInfo[$levelLen-2]);
            $this->setModule(array_slice($pathInfo, 0, $levelLen - 2));
        }
    }

    private function setDefaultRoute(Request $request)
    {
        $routeArr = [
            $this->config['default_module'],
            $this->config['default_controller'],
            $this->config['default_action'],
        ];
        $route = $this->separator . join($this->separator, $routeArr);

        $request->setRoute($route);
    }

    private function setModule($module = [])
    {
        $this->routes['module'] = $module;
    }

    private function setAction($action)
    {
        $this->routes['action'] = $action;
    }

    private function setController($controller)
    {
        $this->routes['controller'] = $controller;
    }

    private function setFormat($format)
    {
        $this->routes['format'] = $format;
    }

}