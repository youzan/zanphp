<?php

namespace Zan\Framework\Network\Http;


class RequestHandler {

    private $route;

    public function __construct()
    {
        $this->route = new Router();
    }

    public function handle(\swoole_http_request $req, \swoole_http_response $resp)
    {
        $request  = $this->buildRequest($req);
        $response = new Response($resp);

        // 1 $params merge to $request
        // 2 $routers 对象化
        list($routes, $params) = $this->route->parse($request);

        (new RequestProcessor($request, $response))->run($routes, $params);
    }

    public function buildRequest($request)
    {
        $requestBuilder = new RequestBuilder($request);
        $requestBuilder->build();

        return $requestBuilder;
    }

}