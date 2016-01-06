<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var \HttpServer
     */
    private $server;

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    public function init()
    {
        parent::init();
        $this->initConfig();
        $this->initHttpServer();
    }

    public function initHttpServer()
    {
        $config = Config::get('http');
        $this->server = new \HttpServer($config);
        $this->server->init();
    }

    private function initConfig()
    {
        Config::init();
        Config::setConfigPath(CONFIG_PATH);
    }

    public function run()
    {
        $this->server->start();
    }

}