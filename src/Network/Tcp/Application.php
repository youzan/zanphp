<?php

namespace Zan\Framework\Network\Tcp;

use \TcpServer;
use Zan\Framework\Foundation\Core\Config;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var TcpServer
     */
    private $server;

    private $serverConfKey = 'tcp.server';

    public function __construct($config)
    {
        parent::__construct($config);
        $this->init();
    }

    public function init()
    {
        parent::init();
        $this->initTcpServer();
    }

    public function initTcpServer()
    {
        $config = Config::get($this->serverConfKey);
        $this->server = new TcpServer($config);
        $this->server->init();
    }

    public function run()
    {
        $this->server->start();
    }

}