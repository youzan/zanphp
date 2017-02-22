<?php

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Nova\Framework\Generic\Service\GenericService;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Network\Server\WorkerStart\InitializeHawkMonitor;
use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\Server\WorkerStart\InitializeServerDiscovery;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use swoole_server as SwooleServer;
use Kdt\Iron\Nova\Nova;
use Kdt\Iron\Nova\Service\ClassMap;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Server\ServerBase;
use Zan\Framework\Network\Tcp\ServerStart\InitializeMiddleware;
use Zan\Framework\Network\Tcp\ServerStart\InitializeSqlMap;
use Zan\Framework\Network\Server\WorkerStart\InitializeWorkerMonitor;
use Zan\Framework\Network\Tcp\WorkerStart\InitializeServerRegister;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\ServerManager\ServiceUnregister;

class Server extends ServerBase {

    protected $serverStartItems = [
        InitializeSqlMap::class,
        InitLogConfig::class,
        InitializeMiddleware::class
    ];

    protected $workerStartItems = [
        InitializeWorkerMonitor::class,
        InitializeConnectionPool::class,
        InitializeServerDiscovery::class,
        InitializeHawkMonitor::class,
    ];

    /**
     * @var SwooleServer
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

        $this->swooleServer->on('connect', [$this, 'onConnect']);
        $this->swooleServer->on('receive', [$this, 'onReceive']);

        $this->swooleServer->on('close', [$this, 'onClose']);

        \swoole_async_set(["socket_dontwait" => 1]);

        $this->init();

        $this->bootServerStartItem();
        
        $this->swooleServer->start();
    }

    private function init()
    {
        // TODO 重新解析 nova.novaApi 支持注册多app
        // TODO 向下兼容
        // TODO haunt.php .app_names 修改成支持 domain 支持拉取 app特定domain内容
        $config = Config::get('nova.novaApi', null);
        if(null === $config){
            return true;
        }

        if(!isset($config['path'])){
            throw new ZanException('nova server path not defined');
        }
        $config['path'] = Path::getRootPath() . $config['path'];

        // TODO 修改Nova...
        Nova::init($config);
        ClassMap::getInstance()->setSpec(GenericService::class, new GenericService());

        $config = Config::get('haunt');
        if (isset($config['app_names']) && is_array($config['app_names']) && [] !== $config['app_names']) {
            ServerStore::getInstance()->resetLockDiscovery();
        }
    }

    public function onConnect()
    {
        sys_echo("connecting ......");
    }

    public function onClose()
    {
        sys_echo("closing .....");
    }

    public function onStart($swooleServer)
    {
        $this->writePid($swooleServer->master_pid);
        Di::make(InitializeServerRegister::class)->bootstrap($this);
        sys_echo("server starting .....");
    }

    public function onShutdown($swooleServer)
    {
        $this->removePidFile();
        (new ServiceUnregister())->unregister();
        sys_echo("server shutdown .....");
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        $this->bootWorkerStartItem($workerId);
        sys_echo("worker #$workerId starting .....");
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        sys_echo("worker #$workerId stopping ....");

        $num = Worker::getInstance()->reactionNum ?: 0;
        sys_echo("worker #$workerId still has $num requests in progress...");
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode, $sigNo)
    {
        sys_echo("worker error happening [workerId=$workerId, workerPid=$workerPid, exitCode=$exitCode, signalNo=$sigNo]...");

        $num = Worker::getInstance()->reactionNum ?: 0;
        sys_echo("worker #$workerId still has $num requests in progress...");
    }

    public function onPacket(SwooleServer $swooleServer, $data, array $clientInfo)
    {
        sys_echo("receive packet data..");
    }

    public function onReceive(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        (new RequestHandler())->handle($swooleServer, $fd, $fromId, $data);
    }
    
}
