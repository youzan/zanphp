<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\Routing\UrlRule;
use Zan\Framework\Network\Http\Server as HttpServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Filter\FilterLoader;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var HttpServer
     */
    private $server;

    private $serverConfKey = 'http.server';

    private $filterConfKey = 'filter';

    public function __construct($config)
    {
        parent::__construct($config);
        self::init();
    }

    public function init()
    {
        parent::init();
        $this->initHttpServer();
        $this->loadFilter();
        $this->loadUrlRules();
    }

    public function initHttpServer()
    {
        $config = Config::get($this->serverConfKey);
        $this->server = new HttpServer($config);
        $this->server->init();
    }

    private function loadUrlRules()
    {
        UrlRule::loadRules($this->config['routing_path']);
    }

    private function loadFilter()
    {
        $filters = Config::get($this->filterConfKey);
        FilterLoader::loadFilter($filters);
    }

    public function run()
    {
        $this->server->start();
    }

}