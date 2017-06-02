<?php

namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Http\Exception\RouteCheckFailedException;
use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Network\Http\Routing\UrlRule;
use swoole_http_request as SwooleHttpRequest;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class RouterSelfCheck
{
    use Singleton;

    public $checkList = [];
    public $urlRules = [];
    public $checkResult;
    public $checkMsg = '';

    public function setUrlRules($urlRules)
    {
        $this->urlRules = $urlRules;
    }

    public function setCheckList($checkList)
    {
        $this->checkList = $checkList;
    }

    public function check()
    {
        $this->checkResult = true;
        $router = Router::getInstance();
        $swooleHttpRequest = new SwooleHttpRequest();
        foreach($this->urlRules as $rule => $target) {
            if(!isset($this->checkList[$rule]) or empty($this->checkList[$rule])) {
                $this->checkResult = false;
                $this->checkMsg = "rule : {$rule} test failed, reason : no testcase";
                break;
            }
            foreach($this->checkList[$rule] as $testRoute => $realRoute) {
                $swooleHttpRequest->server = [
                    'request_uri' => $testRoute,
                ];
                $request = Request::createFromSwooleHttpRequest($swooleHttpRequest);
                $router->route($request);
                $result = $this->_mixRouteResult($request->getRoute(), $request->query->all());
                $realRoute = ltrim($realRoute, '/');
                if($result != $realRoute) {
                    $this->checkResult = false;
                    $this->checkMsg = "rule : {$rule} test failed, reason : realRoute is '{$result}', expected is '{$realRoute}'";
                    break 2;
                }
            }
        }

        if($this->checkResult === false) {
            throw new RouteCheckFailedException("route check fail, {$this->checkMsg}");
        }
    }

    private function _mixRouteResult($route, $parameters)
    {
        if(empty($parameters)) {
            return $route;
        }
        return $route . '?' . http_build_query($parameters);
    }
}