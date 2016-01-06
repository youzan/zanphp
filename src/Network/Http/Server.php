<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\FilterChain;
use Zan\Framework\Network\Server\Registry;

class Server implements \Zan\Framework\Network\Contract\Server {

    public $server = null;

    public function __construct($config=[])
    {
        //TODO valid config
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
        $preFilterChain = FilterChain::loadPreFilters(FILTER_PATH.'/preFilter');
        Registry::set('preFilterChain', $preFilterChain);
    }

    private function initPostFilter()
    {
        FilterChain::loadPostFilters(FILTER_PATH.'/postFilter');
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