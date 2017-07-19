<?php
namespace Zan\Framework\Network\WebSocket;

use Zan\Framework\Foundation\Coroutine\Task;
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
        if ($this->openCallback) {
            $gen = function () use ($req, $request) {
                $cb = $this->openCallback;
                try {
                    yield call_user_func($cb, $req, $request->fd);
                } catch (\Throwable $t) {
                    echo_exception($t);
                } catch (\Exception $e) {
                    echo_exception($e);
                }
            };

            Task::execute($gen());
        }
    }

    public function OnMessage($ws, $frame)
    {
        try {
            $clientIp = $this->clientInfo[$frame->fd];
            $frame->clientIp = $clientIp;
            (new RequestHandler())->handle($ws, $frame);
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }

    public function OnClose($ws, $fd)
    {
        if ($this->closeCallback) {
            $gen = function () use ($fd) {
                $cb = $this->closeCallback;
                try {
                    yield call_user_func($cb, $fd);
                } catch (\Throwable $t) {
                    echo_exception($t);
                } catch (\Exception $e) {
                    echo_exception($e);
                }
            };

            Task::execute($gen());
        }
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