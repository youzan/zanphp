<?php

namespace Zan\Framework\Network\Http;

use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Server as ServerContract;

class Server implements ServerContract
{
    /**
     * @var swooleServer
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

        $this->swooleServer->on('request', [$this, 'onRequest']);

        $this->swooleServer->start();
    }

    public function stop()
    {

    }

    public function reload()
    {

    }

    private function onStart($swooleServer)
    {

    }

    private function onShutdown($swooleServer)
    {

    }

    private function onWorkerStart($swooleServer, $workerId)
    {

    }

    private function onWorkerStop($swooleServer, $workerId)
    {

    }

    private function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {

    }

    private function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
    }
}