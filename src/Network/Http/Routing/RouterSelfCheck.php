<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午5:00
 */

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

    const CHECK_SUCCESS = 'success';
    const CHECK_FAILED = 'failed';
    const OUTPUT_PREFIX = '【RouteSelfCheck】';

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
        $this->checkResult = self::CHECK_SUCCESS;
        $router = Router::getInstance();
        $swooleHttpRequest = new SwooleHttpRequest();
        foreach($this->urlRules as $rule => $target) {
            if(!isset($this->checkList[$rule]) or empty($this->checkList[$rule])) {
                $this->checkResult = self::CHECK_FAILED;
                $this->checkMsg = "rule : {$rule} test failed, reason : no testcase";
                break;
            }
            foreach($this->checkList[$rule] as $testRoute => $realRoute) {
                $swooleHttpRequest->server = [
                    'request_uri' => $testRoute,
                ];
                $request = Request::createFromSwooleHttpRequest($swooleHttpRequest);
                $router->route($request);
                $result = $this->_mixRouteResult($request->getRoute(), $router->getParameters());
                $realRoute = ltrim($realRoute, '/');
                if($result != $realRoute) {
                    $this->checkResult = self::CHECK_FAILED;
                    $this->checkMsg = "rule : {$rule} test failed, reason : realRoute is '{$result}', expected is '{$realRoute}'";
                    break 2;
                }
            }
        }
        $this->output();
    }

    private function _mixRouteResult($route, $parameters)
    {
        if(empty($parameters)) {
            return $route;
        }
        return $route . '?' . http_build_query($parameters);
    }

    protected function output()
    {
        if(self::CHECK_SUCCESS == $this->checkResult) {
            echo self::OUTPUT_PREFIX . 'check success' . PHP_EOL;
        } else {
            throw new RouteCheckFailedException(self::OUTPUT_PREFIX . $this->checkMsg);
        }
    }
}