<?php
namespace Zan\Framework\Network\WebSocket;

use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Network\Http\Server as HttpServer;
use Zan\Framework\Network\Http\ServerStart\InitializeProxyIps;
use Zan\Framework\Network\Http\ServerStart\InitializeRouter;
use Zan\Framework\Network\Http\ServerStart\InitializeSqlMap;
use Zan\Framework\Network\Http\ServerStart\InitializeUrlConfig;
use Zan\Framework\Network\Server\ServerStart\InitLogConfig;
use Zan\Framework\Network\Tcp\ServerStart\InitializeMiddleware;
use Zan\Framework\Network\WebSocket\RequestHandler;

class Server extends HttpServer
{
    private $clientInfo = [];

    /** @var callable */
    private $openCallback = null;

    private $closeCallback = null;

    protected $serverStartItems = [
        InitializeRouter::class,
        InitializeUrlConfig::class,
        InitializeSqlMap::class,
        InitializeMiddleware::class,
        InitLogConfig::class,
        InitializeProxyIps::class,
    ];

    public function setSwooleEvent()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('open', [$this, "onOpen"]);
        $this->swooleServer->on('message', [$this, "OnMessage"]);
        $this->swooleServer->on('close', [$this, "OnClose"]);
    }

    public function onOpen($ws, $request)
    {
        $req = Request::createFromSwooleHttpRequest($request);
        $clientIp = $req->getClientIp();
        $this->clientInfo[$request->fd] = $clientIp;
        if ($this->openCallback)
            call_user_func($this->openCallback, $req, $request->fd);
    }

    public function OnMessage($ws, $frame)
    {
        $clientIp = $this->clientInfo[$frame->fd];
        $frame->clientIp = $clientIp;
        (new RequestHandler())->handle($ws, $frame);
    }

    public function OnClose($ws, $fd)
    {
        if ($this->closeCallback)
            call_user_func($this->closeCallback, $fd);
        unset($this->clientInfo[$fd]);
    }

    /**
     * @param $openCallback
     */
    public function setOpenCallback(callable $openCallback)
    {
        $this->openCallback = $openCallback;
    }

    /**
     * @param $closeCallback
     */
    public function setCloseCallback(callable $closeCallback)
    {
        $this->closeCallback = $closeCallback;
    }

}