<?php

namespace Zan\Framework\Network\Tcp;

use swoole_server as SwooleServer;

class Server {

    /**
     * @var SwooleServer
     */
    private $swooleServer;

    public function __construct(SwooleServer $swooleServer, array $config)
    {
        $this->swooleServer = $swooleServer;
    }

    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('receive', [$this, 'onReceive']);
        $this->swooleServer->on('packet', [$this, 'onPacket']);

        $this->swooleServer->start();
    }

    public function stop()
    {

    }

    public function reload()
    {

    }

    public function onStart($swooleServer)
    {

    }

    public function onShutdown($swooleServer)
    {

    }

    public function onWorkerStart($swooleServer, $workerId)
    {

    }

    public function onWorkerStop($swooleServer, $workerId)
    {

    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {

    }

    public function onPacket(SwooleServer $swooleServer, $data, array $clientInfo)
    {

    }

    public function onReceive(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        (new RequestHandler())->handle($swooleServer, $fd, $fromId, $data);
    }

}