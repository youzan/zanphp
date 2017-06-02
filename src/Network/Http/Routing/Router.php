<?php
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Utilities\DesignPattern\Context;

class Router {

    use Singleton;

    private $config;
    private $url = '';
    private $route = '';
    private $format = '';
    private $rules = [];
    private $parameters = [];
    private $separator = '/';

    private function prepare($url)
    {
        if(empty($url)) {
            return;
        }
        $this->url = ltrim($url, $this->separator);
        $this->removeIllegalString();
        $this->rules = UrlRule::getRules();
    }

    private function clear()
    {
        $this->url = '';
        $this->route = '';
        $this->format = '';
        $this->parameters = [];
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function route(Request $request)
    {
        $requestUri = $request->server->get('REQUEST_URI');
        if(preg_match('/\.ico$/i', $requestUri)){
            return false;
        }

        $this->route = $this->handleUri($requestUri);
        $request->setRoute($this->route);
        $request->setRequestFormat($this->format);
        $this->setParameters($request, $this->parameters);
        $route = $this->parseRoute();
        $this->clear();
        return $route;
    }

    public function handleUri($uri)
    {
        $this->prepare($uri);
        $this->parseRequestFormat($uri);
        empty($this->url) ? $this->setDefaultRoute() : $this->parseRegexRoute();
        $this->repairRoute();

        $rewriteRule = Config::get("rewrite");
        if (is_array($rewriteRule)) {
            foreach ($rewriteRule as $key => $value) {
                $key = ltrim($key, "/");
                $value = ltrim($value, "/");
                if (preg_match('`'.$key.'`', $this->route)) {
                    $this->route = preg_replace('`'.$key.'`', $value, $this->route);
                    break;
                }
            }
        }
        return $this->route;
    }

    private function parseRoute()
    {
        $parts = array_filter(explode($this->separator, trim($this->route, $this->separator)));
        $route['action_name'] = array_pop($parts);
        $route['controller_name'] = join($this->separator, $parts);
        return $route;
    }

    private function parseRequestFormat($requestUri)
    {
        if(false === strpos($requestUri, '.')) {
            $this->setDefaultFormat();
            return;
        }
        $explodeArr = explode('.', $requestUri);
        if(isset($this->config['format_whitelist']) && in_array(end($explodeArr), $this->config['format_whitelist'])){
            $this->format = end($explodeArr);
            array_pop($explodeArr);
            $this->url = implode('.', $explodeArr);
        }else{
            $this->setDefaultFormat();
        }
    }

    private function repairRoute()
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
        $this->parameters = $result['parameter'];
    }

    private function setParameters(Request $request, array $parameters = [])
    {
        if(empty($parameters)) {
            return;
        }
        $request->query->add($parameters);
    }

    public function getParameters()
    {
        return $this->parameters;
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

    private function setDefaultFormat()
    {
        $this->format = $this->getDefaultFormat();
    }

    private function getDefaultFormat()
    {
        return $this->config['default_format'];
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