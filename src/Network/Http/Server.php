<?php

namespace Zan\Framework\Network\Http;

use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Server as ServerContract;
use Zan\Framework\Network\Http\Routing\RouterSelfCheck;
use Zan\Framework\Foundation\Application;

class Server implements ServerContract
{
    /**
     * @var swooleServer
     */
    public $swooleServer;

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

    public function onStart($swooleServer)
    {
        $this->routerSelfCheck();
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

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
    }

    private function routerSelfCheck()
    {
        $basePath = Application::getInstance()->getBasePath();
        $urlRulesPath = $basePath . '/init/routing/';
        $routerSelfCheck = new RouterSelfCheck($urlRulesPath);
        $routerSelfCheck->check();
    }
}