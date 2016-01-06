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

        // todo $routes 对象化
        $routes = $this->route->parse($request);

        (new RequestProcessor($request, $response))->run($routes);
    }

    public function buildRequest($request)
    {
        $requestBuilder = new RequestBuilder($request);
        $requestBuilder->build();

        return $requestBuilder;
    }

}