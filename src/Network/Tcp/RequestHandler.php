<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as SwooleServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Exception\ExcessConcurrencyException;
use Zan\Framework\Network\Exception\ServerTimeoutException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Time;

class RequestHandler
{
    /* @var $swooleServer SwooleServer */
    private $swooleServer;

    /* @var $context Context */
    private $context;

    /* @var $request Request */
    private $request;

    /* @var $response Response */
    private $response;

    private $fd = null;

    private $fromId = null;

    /* @var $task Task */
    private $task;

    /* @var $middleWareManager MiddlewareManager*/
    private $middleWareManager;

    const DEFAULT_TIMEOUT = 30 * 1000;

    public function __construct()
    {
        $this->context = new Context();
        $this->event = $this->context->getEvent();
    }

    public function handle(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        $this->swooleServer = $swooleServer;
        $this->fd = $fd;
        $this->fromId = $fromId;
        $this->doRequest($data);
    }

    private function doRequest($data)
    {
        $request = new Request($this->fd, $this->fromId, $data);
        $response = $this->response = new Response($this->swooleServer, $request);

        $this->context->set('request', $request);
        $this->context->set('swoole_response', $this->response);
        $this->context->set('request_time', Time::stamp());
        $request_timeout = Config::get('server.request_timeout');
        $request_timeout = $request_timeout ? $request_timeout : self::DEFAULT_TIMEOUT;
        $this->context->set('request_timeout', $request_timeout);
        $this->context->set('request_end_event_name', $this->getRequestFinishJobId());

        try {
            $result = $request->decode();
            $this->request = $request;
            if ($request->getIsHeartBeat()) {
                $this->swooleServer->send($this->fd, $result);
                return;
            }
            $request->setStartTime();

            $this->request->getRpcContext()->bindTaskCtx($this->context);
            $this->middleWareManager = new MiddlewareManager($request, $this->context);

            $isAccept = Worker::instance()->reactionReceive();
            //限流
            if (!$isAccept) {
                throw new ExcessConcurrencyException('现在访问的人太多,请稍后再试..', 503);
            }

            $requestTask = new RequestTask($request, $response, $this->context, $this->middleWareManager);
            $coroutine = $requestTask->run();

            //bind event
            $this->event->once($this->getRequestFinishJobId(), [$this, 'handleRequestFinish']);
            Timer::after($request_timeout, [$this, 'handleTimeout'], $this->getRequestTimeoutJobId());

            $this->task = new Task($coroutine, $this->context);
            $this->task->run();
        } catch (\Throwable $t) {
            $this->handleRequestException($response, t2ex($t));
        } catch(\Exception $e) {
            $this->handleRequestException($response, $e);
        }
    }
    private function handleRequestException($response, $e)
    {
            if (Debug::get()) {
                echo_exception($e);
            }

        if ($this->request && $this->request->getServiceName()) {
            $this->reportHawk();
            $this->logErr($e);
        }
            $coroutine = static::handleException($this->middleWareManager, $response, $e);
            Task::execute($coroutine, $this->context);

            $this->event->fire($this->getRequestFinishJobId());
        }

    /**
     * @param $middleware
     * @param \Zan\Framework\Network\Tcp\Response $response
     * @param $t
     */
    public static function handleException($middleware, $response, $t)
    {
        $result = null;
        if ($middleware) {
            $result = (yield $middleware->handleException($t));
        }

        // 兼容PHP5
        if ($result && $result instanceof \Throwable || $result instanceof \Exception) {
            $response->sendException($result);
        } else {
            $response->sendException($t);
        }
    }

    public function handleRequestFinish()
    {
        Timer::clearAfterJob($this->getRequestTimeoutJobId());
        $coroutine = $this->middleWareManager->executeTerminators($this->response);
        Task::execute($coroutine, $this->context);
    }

    public function handleTimeout()
    {
        $this->task->setStatus(Signal::TASK_KILLED);
        $ex = $this->logTimeout();
        $coroutine = static::handleException($this->middleWareManager, $this->response, $ex);
        Task::execute($coroutine, $this->context);
        $this->event->fire($this->getRequestFinishJobId());
    }

    private function logTimeout()
    {
        $request = $this->request;

        if ($request->isGenericInvoke()) {
            $route = $request->getGenericRoute();
            $serviceName = $request->getGenericServiceName();
            $methodName = $request->getGenericMethodName();
        } else {
            $route = $request->getRoute();
            $serviceName = $request->getServiceName();
            $methodName = $request->getMethodName();
        }
        $remoteIp = long2ip($request->getRemoteIp());
        $remotePort = $request->getRemotePort();

        sys_error("SERVER TIMEOUT [remote=$remoteIp:$remotePort, route=$route]");

        $metaData = [
            "isGenericInvoke" => $request->isGenericInvoke(),
            "service"   => $serviceName,
            "method"    => $methodName,
            "args"      => $request->getArgs(),
            "remote"    => "$remoteIp:$remotePort",
        ];

        $ex = new ServerTimeoutException("SERVER TIMEOUT");
        $ex->setMetadata($metaData);

        return $ex;
    }

    private function getRequestFinishJobId()
    {
        return spl_object_hash($this) . '_request_finish';
    }

    private function getRequestTimeoutJobId()
    {
        return spl_object_hash($this) . '_handle_timeout';
    }
}
