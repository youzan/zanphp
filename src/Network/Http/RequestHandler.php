<?php
/**
 * @author hupp
 * create date: 16/01/15
 */

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

        list($routes, $params) = $this->route->parse($request);

        $request->setQueryParams($params);

        try {
            (new RequestProcessor($request, $response))->run($routes);
        }
        catch (\Exception $e) {

            //todo 暂时先这么写，到时候每个类型异常实现handle，这里就调用$e::handle输出
            $error = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'param' => $e->getTrace()[0]['args'],
                'stacktraces' => $e->getTraceAsString()
            ];
            $response->setData($error);
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