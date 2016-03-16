<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Utilities\DesignPattern\Context;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;

class RequestHandler {
    private $context  = null;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        $request  = $this->buildRequest($request);
        Router::getInstance()->route($request);

        $task = new RequestTask($request, $response, $this->context);
        $coroutine = $task->run();

        Task::create($coroutine, $this->context);
    }

    private function buildRequest($request)
    {
        $requestBuilder = new RequestBuilder($request);

        return $requestBuilder->build();
    }
}