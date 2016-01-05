<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\FilterChain;

class Server implements \Zan\Framework\Network\Contract\Server {

    public $server = null;

    public function __construct($config=[])
    {
        $this->server = new \swoole_http_server($config['host'], $config['port']);
        $this->setServerConfig($config);
        $this->bindRequestEvents();
    }

    public function init()
    {
        $this->initPreFilter();
        $this->initPostFilter();
    }

    private function initPreFilter()
    {
        FilterChain::loadPreFilters(FILTER_PATH.'/preFilter');
    }

    private function initPostFilter()
    {
        FilterChain::loadPostFilters(FILTER_PATH.'/postFilter');
    }

    private function bindRequestEvents()
    {
        $this->server->on('Request', [new RequestHandler(), 'onRequest']);
    }

    private function setServerConfig($config)
    {
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