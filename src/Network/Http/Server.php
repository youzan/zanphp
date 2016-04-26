<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\ServerStart\InitializeRouter;
use Zan\Framework\Network\Http\ServerStart\InitializeExceptionHandlerChain;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use Zan\Framework\Network\Server\WorkerStart\InitializeWorkerMonitor;
use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Server as ServerContract;
use Zan\Framework\Network\Http\Routing\RouterSelfCheck;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Network\Server\ServerBase;

class Server extends ServerBase implements ServerContract
{
    protected $serverStartItems = [
        InitializeRouter::class,
        InitializeExceptionHandlerChain::class
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
        InitializeWorkerMonitor::class
    ];

    /**
     * @var swooleServer
     */
    public $swooleServer;

    public function __construct(SwooleServer $swooleServer, array $config)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->
        set(['worker_num' => $config['worker_num']]);
    }

    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('request', [$this, 'onRequest']);

        $this->bootServerStartItem();

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
        $this->bootWorkerStartItem($workerId);
    }

    public function onWorkerStop($swooleServer, $workerId)
    {

    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {

    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
//        try {
            (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
//        } catch (\Exception $e) {
//            RequestExceptionHandlerChain::getInstance()->handle($e, $swooleHttpRequest, $swooleHttpResponse);
//        }
    }

    private function routerSelfCheck()
    {
        $basePath = Application::getInstance()->getBasePath();
        $urlRulesPath = $checkListPath = $basePath . '/init/routing/';
        $routerSelfCheck = new RouterSelfCheck($urlRulesPath, $checkListPath);
        $routerSelfCheck->check();
    }
}
