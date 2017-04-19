<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\ServerStart\InitializeRouter;
use Zan\Framework\Network\Http\ServerStart\InitializeUrlRule;
use Zan\Framework\Network\Http\ServerStart\InitializeRouterSelfCheck;
use Zan\Framework\Network\Http\ServerStart\InitializeMiddleware;
use Zan\Framework\Network\Http\ServerStart\InitializeExceptionHandlerChain;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use Zan\Framework\Network\Server\WorkerStart\InitializeExceptionHandler;
use Zan\Framework\Network\Server\WorkerStart\InitializeHawkMonitor;
use Zan\Framework\Network\Server\WorkerStart\InitializeWorkerMonitor;
use Zan\Framework\Network\Server\WorkerStart\InitializeServerDiscovery;
use Zan\Framework\Network\Http\ServerStart\InitializeUrlConfig;
use Zan\Framework\Network\Http\ServerStart\InitializeQiniuConfig;
use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Server as ServerContract;
use Zan\Framework\Network\Server\ServerBase;
use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\ServerManager\ServerDiscoveryInitiator;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\ServerStart\InitializeSqlMap;

class Server extends ServerBase implements ServerContract
{
    protected $serverStartItems = [
        InitializeRouter::class,
        InitializeUrlRule::class,
        InitializeUrlConfig::class,
        InitializeQiniuConfig::class,
        InitializeRouterSelfCheck::class,
        InitializeMiddleware::class,
        InitializeExceptionHandlerChain::class,
        InitLogConfig::class,
        InitializeSqlMap::class,
    ];

    protected $workerStartItems = [
        InitializeExceptionHandler::class,
        InitializeWorkerMonitor::class,
        InitializeHawkMonitor::class,
        InitializeConnectionPool::class,
        InitializeServerDiscovery::class,
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

        \swoole_async_set(["socket_dontwait" => 1]);

        $this->bootServerStartItem();
        $this->init();
        $this->swooleServer->start();
    }

    private function init()
    {
        $config = Config::get('haunt');
        if (!isset($config['app_names']) || [] === $config['app_names']) {
            return;
        }
        ServerStore::getInstance()->resetLockDiscovery();
    }

    public function stop()
    {

    }

    public function reload()
    {

    }

    public function onStart($swooleServer)
    {
        $this->writePid($swooleServer->master_pid);
        sys_echo("server starting .....[$swooleServer->host:$swooleServer->port]");
    }

    public function onShutdown($swooleServer)
    {
        $this->removePidFile();
        sys_echo("server shutdown .....");
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        $_SERVER["WORKER_ID"] = $workerId;
        $this->bootWorkerStartItem($workerId);
        sys_echo("worker *$workerId starting .....");
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);
        sys_echo("worker *$workerId stopping .....");

        $num = Worker::getInstance()->reactionNum ?: 0;
        sys_echo("worker *$workerId still has $num requests in progress...");
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode, $sigNo)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);

        sys_echo("worker error happening [workerId=$workerId, workerPid=$workerPid, exitCode=$exitCode, signalNo=$sigNo]...");

        $num = Worker::getInstance()->reactionNum ?: 0;
        sys_echo("worker *$workerId still has $num requests in progress...");
    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
//        try {
            (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
//        } catch (\Exception $e) {
//            RequestExceptionHandlerChain::getInstance()->handle($e, $swooleHttpRequest, $swooleHttpResponse);
//        }
    }
}
