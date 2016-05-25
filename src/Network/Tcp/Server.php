<?php

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;
use swoole_server as SwooleServer;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Server\ServerBase;
use Zan\Framework\Network\Tcp\ServerStart\InitializeSqlMap;

class Server extends ServerBase {

    protected $serverStartItems = [
        InitializeSqlMap::class
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
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
    }

    private function registerServices()
    {
        $config = Config::get('nova.platform');
        $config['services'] = Nova::getAvailableService();

        $appName = Application::getInstance()->getName();
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
        echo "server start .....\n";
    }

    public function onShutdown($swooleServer)
    {
        echo "server shutdown .....\n";
    }

    public function onWorkerStart($swooleServer, $workerId)
    {
        $this->bootWorkerStartItem($workerId);
        
        echo "worker starting .....\n";
    }

    public function onWorkerStop($swooleServer, $workerId)
    {
        echo "worker stoping ....\n";
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
