<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;

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
        if (!($uri = $this->request->getRequestUri())) {
            $this->setDefaultRoute();
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

    }

    private function completeResult()
    {

    }

    protected function parseStringRoute()
    {

    }

    private function setDefaultModule()
    {
        $this->routes['module'] = $this->config['default_module'];
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