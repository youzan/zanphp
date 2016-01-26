<?php

namespace Zan\Framework\Network\Http;

use \swoole_http_server as HttpServer;

class Server implements \Zan\Framework\Network\Contract\Server {

    /**
     * @var HttpServer
     */
    public $server;

    public function __construct($config=[])
    {
        $this->server = new HttpServer($config['host'], $config['port']);
        $this->setServerConfig($config);
    }

    public function init()
    {
        $this->bindRequestEvents();
    }

    private function bindRequestEvents()
    {
        $this->server->on('Request', [new RequestHandler(), 'handle']);
    }

    private function setServerConfig($config)
    {
        $acceptKeys = [];
        $this->server->set($config);
    }

    public function start()
    {
        $this->server->start();
    }

    public function stop()
    {

    }

    public function reload()
    {

    }
}