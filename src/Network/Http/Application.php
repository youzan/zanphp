<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\FilterChain;
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
        $this->initConfig();
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

    private function initConfig()
    {
        Config::init();
        Config::setConfigPath($this->config['config_path']);
    }

    public function run()
    {
        $this->server->start();
    }

}