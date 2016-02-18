<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Router\Regex;

class Router extends \Zan\Framework\Network\Contract\Router {

    protected $request;
    protected $config;
    protected $url;
    protected $routes = [];

    public function __construct()
    {
        $this->config  = Config::get('route');
    }

    public function parse(Request $request)
    {
        $this->request = $request;
        $this->setUrl($this->request->getRequestUri());
        $this->setDefaultRoute();

        if (!$this->url) {
            return $this->routes;
        }
        $this->parseRegexRoute();
        $this->parseStringRoute();

        return $this->routes;
    }

    private function setDefaultRoute()
    {
        $this->setDefaultModule();
        $this->setDefaultController();
        $this->setDefaultAction();
        $this->setDefaultFormat();
    }

    private function parseRegexRoute()
    {
        $route  = (new Regex())->decode($this->url);
        if ($route) {
            $this->completeResult($route);
            if (!isset($route['url'])){
                return true;
            }
            $this->url = ltrim($route['url'],'/');
        }
        return false;
    }

    private function setUrl($url)
    {
        $this->url = $url;
    }

    private function completeResult($data)
    {
        $keys = ['module','controller','action','format'];
        foreach($keys as $key){
            if(isset($data[$key])){
                $this->routes[$key] = $data[$key];
            }else{
                $action = 'setDefault' . ucfirst($key);
                $this->$action();
            }
        }
        if(isset($data['parameter']) && !empty($data['parameter'])){
            foreach($data['parameter'] as $k => $v){
                $_GET[$k] = $v;
                $_REQUEST[$k] = $v;
            }
        }
    }

    private function parseAction($action)
    {
        $pos = strpos($action,'.');
        if(false === $pos){
            $this->routes['action']  = $action;
            $this->setDefaultFormat();
        }else{
            $this->routes['action']  = substr($action, 0, $pos);
            $this->routes['format']  = substr($action, $pos + 1);
        }
    }

    protected function parseStringRoute($url=null)
    {
        if (null === $url){
            $url = $this->url;
        }
        $path = explode('/', ltrim($url, '/'));
        $len  = count($path);

        if($len > 0){
            $this->routes['module'] = $path[0];
        }
        if($len > 1){
            $this->routes['controller'] = ucfirst($path[1]);
        }
        if($len > 2){
            $this->parseAction($path[2]);
        }
    }

    private function setDefaultModule()
    {
        $this->routes['module'][] = $this->config['default_module'];
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