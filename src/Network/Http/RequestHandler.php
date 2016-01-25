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
        $response = $this->buildResponse($resp);

        $routes = $this->route->parse($request);

        (new RequestProcessor($request, $response))->run($routes);
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