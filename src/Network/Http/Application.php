<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/31
 * Time: 23:55
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var \HttpServer
     */
    private $server;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    public function init()
    {
        parent::init();
        $this->getHttpConfig();
        $this->initConfig();
        $this->initHttpServer();
    }

    public function initHttpServer()
    {
        $this->server = new \HttpServer($this->config);
        $this->server->init();
    }

    private function initConfig()
    {
        Config::init();
        Config::setConfigPath(CONFIG_PATH);
    }

    private function getHttpConfig()
    {
        $this->config = Config::get('http');
    }

    public function run()
    {
        $this->server->start();
    }

}