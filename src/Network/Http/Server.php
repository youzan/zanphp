<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\ServerStart\InitializeRouter;
use Zan\Framework\Network\Http\ServerStart\InitializeUrlRule;
use Zan\Framework\Network\Http\ServerStart\InitializeMiddleware;
use Zan\Framework\Network\Http\ServerStart\InitializeCache;
use Zan\Framework\Network\Http\ServerStart\InitializeExceptionHandlerChain;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Server as ServerContract;
use Zan\Framework\Network\Server\ServerBase;

class Server extends ServerBase implements ServerContract
{
    protected $serverStartItems = [
        InitializeRouter::class,
        InitializeUrlRule::class,
        InitializeMiddleware::class,
        InitializeExceptionHandlerChain::class,
        InitializeCache::class
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
    ];

    /**
     * @var swooleServer
     */
    public $swooleServer;

    public function __construct(SwooleServer $swooleServer, array $config)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
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
        echo "http server start ......\n";
    }

    public function onShutdown($swooleServer)
    {
        echo "http server shutdown ...... \n";
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        echo "http worker start ..... \n";
        $this->bootWorkerStartItem($workerId);
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        echo "http worker stop ..... \n";
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {
        echo "http worker error ..... \n";
    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
    }
}
