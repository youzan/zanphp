<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as SwooleServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Network\Exception\ExcessConcurrencyException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Time;

class RequestHandler {
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

            $rpcCtx = $this->request->getRpcContext();
            foreach ($rpcCtx->get() as $k => $v) {
                $this->context->set($k, $v);
            };
            $this->context->set(RpcContext::KEY, $rpcCtx);

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
        } catch(\Exception $e) {
            if (Debug::get()) {
                echo_exception($e);
            }
            $response->sendException($e);
            $this->event->fire($this->getRequestFinishJobId());
            return;
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
        printf(
            "[%s] TIMEOUT %s %s\n",
            Time::current('Y-m-d H:i:s'),
            $this->request->getRoute(),
            http_build_query($this->request->getArgs())
        );

        $this->task->setStatus(Signal::TASK_KILLED);
        $e = new \Exception('server timeout');
        $this->response->sendException($e);
        $this->event->fire($this->getRequestFinishJobId());
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
