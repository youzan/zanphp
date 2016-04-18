<?php

namespace Zan\Framework\Network\Http;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Http\Request\Request;

class RequestHandler
{
    private $context = null;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(SwooleHttpRequest $swooleRequest, SwooleHttpResponse $swooleResponse)
    {
        try {

            $request = Request::createFromSwooleHttpRequest($swooleRequest);
            $this->context->set('request', $request);
            $this->context->set('response', $swooleResponse);

            $router = Router::getInstance();
            $router->route($request);
            $route = $router->parseRoute();
            $this->context->set('controller_name', $route['controller_name']);
            $this->context->set('action_name', $route['action_name']);

            $cookie = new Cookie($request, $swooleResponse);
            $this->context->set('cookie', $cookie);

            $task = new RequestTask($request, $swooleResponse, $this->context);
            $coroutine = $task->run();
            Task::execute($coroutine, $this->context);

        } catch (\Exception $e) {
            $coroutine = RequestExceptionHandlerChain::getInstance()->handle($e);
            Task::execute($coroutine, $this->context);
        }

    }
}
