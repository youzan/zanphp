<?php
/**
 * @author hupp
 * create date: 16/01/15
 */
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request;

class Router {

    const BASIC_LEVEL = 3;

    private $config;
    private $url;
    private $rules  = [];
    private $routes = [];
    private $routeConKey = 'route';

    public function __construct($config = [])
    {
        $this->config = $config;

        if (!$this->config) {
            $this->config = Config::get($this->routeConKey);
        }
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
        list($url, $params) = $this->parseRegexRoute();
        $this->setRoutes($url);

        quit:
        return [
            $this->routes,
            isset($params) ? $params : []
        ];
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

    private function setFormat($format)
    {
        $this->routes['format'] = $format;
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