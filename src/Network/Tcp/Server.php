<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as TcpServer;

class Server implements \Zan\Framework\Network\Contract\Server {

    /**
     * @var TcpServer
     */
    private $server;

    public function __construct($config=[])
    {
        $this->server = new TcpServer($config['host'], $config['port']);
        $this->setServerConfig($config['option']);
    }

    public function init()
    {
        $this->bindRequestEvents();
    }

    private function bindRequestEvents()
    {
        $this->server->on('receive', [new ReceiveHandler(), 'handle']);
    }

    private function setServerConfig(array $serverOption)
    {
        $this->server->set($serverOption);
    }

    public function start()
    {
        $this->server->start();
    }

    public function stop()
    {
        $this->server->shutdown();
    }

    /**
     * only reload task worker
     */
    public function reload()
    {
        $this->server->reload();
    }
}