<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Server implements \Zan\Framework\Network\Contract\Server {

    public $server = null;

    public function __construct($config=[])
    {
        $this->validServerConfig($config);
        $this->server = new \swoole_http_server($config['host'], $config['port']);
        $this->setServerConfig($config);
    }

    public function init()
    {
        $this->bindRequestEvents();
    }

    private function validServerConfig($config)
    {
        if (!isset($config['host']) ||
            !filter_var($config['host'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new InvalidArgument('Invalid IP address!');
        }
        if (!isset($config['port']) || !$config['port']) {
            throw new InvalidArgument('Invalid port!');
        }
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