<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestHandler {
    private $response = null;
    private $context  = null;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(\swoole_http_request $req, \swoole_http_response $resp)
    {
        $request  = $this->buildRequest($req);
        $this->response = $this->buildResponse($resp);

        Router::getInstance()->route($this->request);


    }

    private function buildRequest($request)
    {
        $requestBuilder = new RequestBuilder($request);

        return $requestBuilder->build();
    }

    private function buildResponse($response)
    {
        return new Response($response);
    }

}