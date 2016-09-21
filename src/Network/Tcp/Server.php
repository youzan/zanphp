<?php

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Network\ServerManager\ServerStore;
use Zan\Framework\Network\Server\WorkerStart\InitializeServerDiscovery;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use swoole_server as SwooleServer;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Server\ServerBase;
use Zan\Framework\Network\Tcp\ServerStart\InitializeSqlMap;
use Zan\Framework\Network\Server\WorkerStart\InitializeWorkerMonitor;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Tcp\WorkerStart\InitializeServerRegister;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\ServerManager\ServiceUnregister;

class Server extends ServerBase {

    protected $serverStartItems = [
        InitializeSqlMap::class,
        InitLogConfig::class,
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
        InitializeWorkerMonitor::class,
        InitializeServerDiscovery::class,
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
        $this->registerServices();
        
        $this->bootServerStartItem();
        
        $this->swooleServer->start();
    }

    private function init()
    {
        $config = Config::get('nova.novaApi', null);
        if(null === $config){
            return true;
        }

        if(!isset($config['path'])){
            throw new ZanException('nova server path not defined');
        }
        $config['path'] = Path::getRootPath() . $config['path'];
        Nova::init($config);

        $config = Config::get('haunt');
        if (isset($config['app_names']) && is_array($config['app_names']) && [] !== $config['app_names']) {
            ServerStore::getInstance()->resetLockDiscovery();
        }
    }

    private function registerServices()
    {
        $appName = Application::getInstance()->getName();
        $config = Config::get('server.hawk_collection');
        $config['module'] = $appName;
        $this->swooleServer->nova_config($config);
    }

    public function onConnect()
    {
        echo "connecting ......\n";
    }

    public function onClose()
    {
        echo "closing .....\n";
    }

    public function onStart($swooleServer)
    {
        $this->writePid($swooleServer->master_pid);
        Di::make(InitializeServerRegister::class)->bootstrap($this);
        echo "server starting .....\n";
    }

    public function onShutdown($swooleServer)
    {
        $this->removePidFile();
        (new ServiceUnregister())->unregister();
        echo "server shutdown .....\n";
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        $this->bootWorkerStartItem($workerId);
        echo "worker #$workerId starting .....\n";
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        echo "worker #$workerId stopping ....\n";
    }

    public function onWorkerError($swooleServer, $workerId, $workerPid, $exitCode)
    {
        echo "worker error happening ....\n";
    }

    public function onPacket(SwooleServer $swooleServer, $data, array $clientInfo)
    {
        echo "receive packet data\n\n\n\n";
    }

    public function onReceive(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        (new RequestHandler())->handle($swooleServer, $fd, $fromId, $data);
    }
    
}
