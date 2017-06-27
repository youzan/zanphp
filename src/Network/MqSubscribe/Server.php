<?php

namespace Zan\Framework\Network\MqSubscribe;

use Zan\Framework\Network\Http\RequestHandler;
use Zan\Framework\Network\MqSubscribe\WorkerStart\InitializeMqSubscribe;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use Zan\Framework\Network\Server\WorkerStart\InitializeErrorHandler;
use Zan\Framework\Network\Server\WorkerStart\InitializeWorkerMonitor;
use Zan\Framework\Network\Server\WorkerStart\InitializeServerDiscovery;
use swoole_http_server as SwooleServer;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Network\Server\ServerBase;
use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\ServerManager\ServerDiscoveryInitiator;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Tcp\ServerStart\InitializeSqlMap;

class Server extends ServerBase
{
    protected $serverStartItems = [
        InitializeSqlMap::class,
        InitLogConfig::class,
    ];

    protected $workerStartItems = [
        InitializeErrorHandler::class,
        InitializeConnectionPool::class,
        InitializeWorkerMonitor::class,
        InitializeServerDiscovery::class,
        InitializeMqSubscribe::class,
    ];

    public function setSwooleEvent()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('request', [$this, 'onRequest']);
    }

    protected function init()
    {
        $config = Config::get('registry');
        if (!isset($config['app_names']) || [] === $config['app_names']) {
            return;
        }
        ServerStore::getInstance()->resetLockDiscovery();
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
        sys_echo("worker *$workerId starting .....");
        (new MqSubscribe())->start();
        sys_echo("mq subscribe in worker *$workerId starting .....");
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);
        sys_echo("worker *$workerId stopping .....");
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode, $sigNo)
    {
        ServerDiscoveryInitiator::getInstance()->unlockDiscovery($workerId);
    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        (new RequestHandler())->handle($swooleHttpRequest, $swooleHttpResponse);
    }
}
