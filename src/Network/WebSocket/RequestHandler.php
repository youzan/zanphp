<?php
namespace Zan\Framework\Network\WebSocket;

use \swoole_websocket_server as SwooleWebSocketServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Signal;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Exception\ExcessConcurrencyException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Json;
use Zan\Framework\Utilities\Types\Time;


class RequestHandler {
    /* @var $swooleWebSocketServer SwooleWebSocketServer */
    private $swooleWebSocketServer;
    /* @var $context Context */
    private $context;
    /* @var $request Request */
    private $request;
    /* @var $response Response */
    private $response;
    private $fd;
    private $opcode;

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

    public function handle(SwooleWebSocketServer $swooleWebSocketServer, $frame)
    {
        $this->swooleWebSocketServer = $swooleWebSocketServer;
        $this->fd = $frame->fd;
        $this->opcode = $frame->opcode;

        $this->response = new Response($swooleWebSocketServer, $frame->fd);

        $this->context->set('client_ip', $frame->clientIp);

        if ($frame->finish != true) {
            $this->response->fail(Response::ERR_CODE_CONTINUE_UNSUPPORTED);
            return;
        }
        $input = Json::decode($frame->data);
        if (!isset($input['path']) || !isset($input['data'])) {
            $this->response->fail(Response::ERR_CODE_ARGS_INVALID);
            return;
        }

        $path = $input['path'];
        $data = $input['data'];

        $this->doRequest($path, $data);
    }

    private function doRequest($path, $data)
    {
        $request = new Request($this->fd, $this->opcode, $path, $data);

        $route = $request->getRoute();

        $parts = array_filter(explode("/", trim($route, "/")));
        $action = array_pop($parts);
        $controller = join("/", $parts);

        $this->context->set('controller_name', $controller);
        $this->context->set('action_name', $action);
        $this->context->set('request', $request);
        $this->context->set('swoole_response', $this->response);
        $this->context->set('request_time', Time::stamp());
        $request_timeout = Config::get('server.request_timeout');
        $request_timeout = $request_timeout ? $request_timeout : self::DEFAULT_TIMEOUT;
        $this->context->set('request_timeout', $request_timeout);
        $this->context->set('request_end_event_name', $this->getRequestFinishJobId());

        try {
            $this->request = $request;

            $request->setStartTime();

            $this->middleWareManager = new MiddlewareManager($request, $this->context);

            $isAccept = Worker::instance()->reactionReceive();
            //限流
            if (!$isAccept) {
                throw new ExcessConcurrencyException('现在访问的人太多,请稍后再试..', 503);
            }

            //bind event
            $this->event->once($this->getRequestFinishJobId(), [$this, 'handleRequestFinish']);
            Timer::after($request_timeout, [$this, 'handleTimeout'], $this->getRequestTimeoutJobId());

            $requestTask = new RequestTask($request, $this->context, $this->middleWareManager);
            $coroutine = $requestTask->run();

            $this->task = new Task($coroutine, $this->context);
            $this->task->run();
        } catch (\Throwable $t) {
            $this->handleRequestException(t2ex($t));
        } catch (\Exception $e) {
            $this->handleRequestException($e);
        }
    }

    private function handleRequestException($e)
    {
        if (Debug::get()) {
            echo_exception($e);
        }

        $coroutine = static::handleException($this->middleWareManager, $e);
        Task::execute($coroutine, $this->context);

        $this->event->fire($this->getRequestFinishJobId());
    }

    public static function handleException(MiddlewareManager $middleware, \Exception $e)
    {
        $result = null;
        if ($middleware) {
            $result = (yield $middleware->handleException($e));
        }

        /** @var Response $response */
        $response = (yield getContext("swoole_response"));

        if ($result instanceof \Throwable || $result instanceof \Exception) {
            $response->fail($result->getCode(), $result->getMessage());
        } else {
            $response->fail($e->getCode(), $e->getMessage());
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
        try {
            $this->task->setStatus(Signal::TASK_KILLED);
            $this->logTimeout();

            /** @var Response $response */
            $response = $this->context->get('swoole_response');
            $response->fail(Response::ERR_CODE_REQUEST_TIMEOUT, "请求超时");
            $this->event->fire($this->getRequestFinishJobId());
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $ex) {
            echo_exception($ex);
        }
    }

    private function logTimeout()
    {
        // 注意: 此处需要配置 server.proxy
        $remoteIp = $this->context->get("client_ip", "");
        $route = $this->request->getRoute();
        sys_error("SERVER TIMEOUT [remoteIP=$remoteIp, path=$route]");
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