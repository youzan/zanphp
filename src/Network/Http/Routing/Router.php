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

    private $config;
    private $url;
    private $route = '';
    private $rules = [];
    private $routeConKey = 'route';
    private $separator = '/';

    public function __construct($config = [])
    {
        $this->config = $config;
        if (!$this->config) {
            $this->config = Config::get($this->routeConKey);
        }
        $this->rules = UrlRule::getRules();
    }

    private function prepare($url)
    {
        if(empty($url)) {
            return;
        }
        $this->url = ltrim($url, $this->separator);
        $this->removeIllegalString();
    }

    public function route(Request $request)
    {
        $this->prepare($request->server->get('REQUEST_URI'));
        empty($this->url) ? $this->setDefaultRoute() : $this->parseRegexRoute();
        $this->checkAndRepairRoute();
        $this->route = explode('.', $this->route);
        $this->route = $this->route[0];
        $request->setRoute($this->route);
    }

    private function checkAndRepairRoute()
    {
        $path = array_filter(explode($this->separator, $this->route));
        $pathCount = count($path);
        switch($pathCount)
        {
            case 0:
                $this->setDefaultRoute();
                break;
            case 1:
                $this->setDefaultControllerAndDefaultAction();
                break;
            case 2:
                $this->setDefaultAction();
                break;
        }
    }

    private function parseRegexRoute()
    {
        $rules = UrlRegex::formatRules($this->rules);
        $result = UrlRegex::decode($this->url, $rules);
        $this->route = ltrim($result['url'], $this->separator);
        $this->setParameters($result['parameter']);
    }

    private function setParameters(array $parameters = [])
    {
        if(empty($parameters)) {
            return;
        }
        foreach($parameters as $k => $v) {
            $_GET[$k] = $v;
            $_REQUEST[$k] = $v;
        }
    }

    private function setDefaultRoute()
    {
        $this->route = $this->getDefaultRoute();
    }

    private function getDefaultRoute()
    {
        return $this->config['default_route'];
    }

    private function setDefaultControllerAndDefaultAction()
    {
        $path = array_filter(explode($this->separator, $this->route));
        array_push($path, $this->getDefaultController(), $this->getDefaultAction());
        $this->route = join($this->separator, $path);
    }

    private function getDefaultController()
    {
        return $this->config['default_controller'];
    }

    private function setDefaultAction()
    {
        $path = array_filter(explode($this->separator, $this->route));
        array_push($path, $this->getDefaultAction());
        $this->route = join($this->separator, $path);
    }

    private function getDefaultAction()
    {
        return $this->config['default_action'];
    }

    private function removeIllegalString()
    {
        $patterns   = [
            '/^\s*\/\//','/\?.*$/','/\#.*$/','/\/\s*$/','/^\s*\//'
        ];
        $replaces   = [
            '','','','','',
        ];
        $this->url = preg_replace($patterns, $replaces, $this->url);
    }
}