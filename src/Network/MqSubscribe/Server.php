<?php

namespace Zan\Framework\Network\MqSubscribe;

use Zan\Framework\Network\Http\RequestHandler;
use Zan\Framework\Network\Http\ServerStart\InitializeRouter;
use Zan\Framework\Network\Http\ServerStart\InitializeUrlRule;
use Zan\Framework\Network\Http\ServerStart\InitializeRouterSelfCheck;
use Zan\Framework\Network\Http\ServerStart\InitializeMiddleware;
use Zan\Framework\Network\Http\ServerStart\InitializeExceptionHandlerChain;
use Zan\Framework\Network\MqSubscribe\WorkerStart\InitializeMqSubscribe;
use Zan\Framework\Network\MqSubscribe\WorkerStart\MqSubscribeStart;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
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
use Zan\Framework\Network\Tcp\ServerStart\InitializeSqlMap;

class Server extends ServerBase implements ServerContract
{
    protected $serverStartItems = [
        InitializeSqlMap::class,
        InitLogConfig::class,
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
        InitializeWorkerMonitor::class,
        InitializeServerDiscovery::class,

        InitializeMqSubscribe::class,
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
        sys_echo("server starting .....");
    }

    public function onShutdown($swooleServer)
    {
        $this->removePidFile();
        sys_echo("server shutdown .....");
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        $this->bootWorkerStartItem($workerId);
        sys_echo("worker #$workerId starting .....");
        (new MqSubscribe())->start();
        sys_echo("mq subscribe in worker #$workerId starting .....");
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);
        sys_echo("worker #$workerId stopping .....");
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);
    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
    }
}
