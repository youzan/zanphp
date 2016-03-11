<?php
/**
 * @author hupp
 * create date: 16/01/15
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Http\Routing\Router;

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

        list($routes, $params) = $this->route->parse($request);

        $request->setQueryParams($params);

        try {
            (new RequestProcessor($request, $response))->run($routes);
        }
        catch (ZanException $e)
        {
            $e->handle($e);
            $response->setData($e->getFormatMessage($e));
            $response->send();
        }
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