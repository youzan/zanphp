<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\FilterChain;
use Zan\Framework\Foundation\Exception\Handler;
use Zan\Framework\Network\Server\Registry;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var \HttpServer
     */
    private $server;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->init();
    }

    public function init()
    {
        parent::init();
        $this->initErrorHandler();
        $this->initHttpServer();
        $this->initPreFilter();
        $this->initPostFilter();
    }

    public function initHttpServer()
    {
        $config = Config::get('http');
        $this->server = new \HttpServer($config);
        $this->server->init();
    }

    private function initErrorHandler()
    {
        Handler::initErrorHandler();
    }

    private function initPreFilter()
    {
        $preFilterChain = FilterChain::loadPreFilters($this->config['PreFilter']);
        Registry::set('preFilterChain', $preFilterChain);
    }

    private function initPostFilter()
    {
        $postFilterChain = FilterChain::loadPostFilters($this->config['PostFilter']);
        Registry::set('postFilterChain', $postFilterChain);
    }

    public function run()
    {
        $this->server->start();
    }

}