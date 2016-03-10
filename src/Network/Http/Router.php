<?php
/**
 * @author hupp
 * create date: 16/01/15
 */
namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Router\UrlRegex;
use Zan\Framework\Network\Http\Router\UrlRule;

class Router extends \Zan\Framework\Network\Contract\Router {

    const BASIC_LEVEL = 3;

    private $config;
    private $url;
    private $rules  = [];
    private $routes = [];
    private $routeConKey = 'route';

    public function __construct()
    {
        $this->config = Config::get($this->routeConKey);
        $this->rules  = UrlRule::getRules();
    }

    public function parse(Request $request)
    {
        $this->url = ltrim($request->getUrl(), '/');
        $this->setMethod($request->getMethod());

        if (!$this->url) {
            $this->setDefaultRoute();
            goto quit;
        }
        $routes = $this->parseRegexRoute();
        $this->setRoutes($routes['url']);

        quit:
        return [
            $this->routes,
            isset($routes['params']) ? $routes['params'] : []
        ];
    }

    private function parseRegexRoute()
    {
        $rules = UrlRegex::formatRules($this->rules);
        $route = UrlRegex::decode($this->url, $rules);

        return [
            'url'    => isset($route['url']) ? $route['url'] : $this->url,
            'params' => isset($route['parameter']) ? $route['parameter'] : [],
        ];
    }

    private function setRoutes($url)
    {
        $pathInfo = explode('/', ltrim($url, '/'));
        $pathInfo = array_filter($pathInfo);
        $levelLen = count($pathInfo);

        if ($levelLen >= self::BASIC_LEVEL) {
            $action = end($pathInfo);
            if (strpos($action,'.') === false) {
                $this->setAction($action);
                $this->setDefaultFormat();
            }
            $this->setController($pathInfo[$levelLen-2]);
            $this->setModule(array_slice($pathInfo, 0, $levelLen - 2));
        }
    }

    private function setDefaultRoute()
    {
        $this->setDefaultModule();
        $this->setDefaultController();
        $this->setDefaultAction();
        $this->setDefaultFormat();
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

    private function setMethod($method)
    {
        $this->routes['method'] = strtolower($method);
    }

    private function setDefaultModule()
    {
        $this->routes['module'] = [$this->config['default_module']];
    }

    private function setDefaultController()
    {
        $this->routes['controller'] = $this->config['default_controller'];
    }

    private function setDefaultAction()
    {
        $this->routes['action'] = $this->config['default_action'];
    }

    private function setDefaultFormat()
    {
        $this->routes['format'] = $this->config['default_format'];
    }
}