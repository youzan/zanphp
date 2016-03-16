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
    private $context  = null;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(SwooleHttpRequest $swooleRequest, SwooleHttpResponse $swooleResponse)
    {
        echo "swoole handle\n\n\n";
        $swooleResponse->end('Hello world');
        return ;
        $request  = Request::createFromSwooleHttpRequest($swooleRequest);
        Router::getInstance()->route($request);

        $task = new RequestTask($request, $swooleResponse, $this->context);
        $coroutine = $task->run();

        Task::create($coroutine, $this->context);
    }
}