<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午5:00
 */

namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Network\Http\Routing\Router;
use Zan\Framework\Network\Http\Routing\UrlRule;
use swoole_http_request as SwooleHttpRequest;
use Zan\Framework\Network\Http\Request\Request;

class RouterSelfCheck
{
    public $urlRulesPath = '';
    public $defaultRouteConfPath = '';
    public $checkResult;

    const CHECK_SUCCESS = 'success';
    const CHECK_FAILED = 'failed';

    public function __construct($urlRulesPath, $defaultRouteConfPath)
    {
        $this->urlRulesPath = $urlRulesPath;
        $this->defaultRouteConfPath = $defaultRouteConfPath;
        $this->checkResult = self::CHECK_SUCCESS;
    }

    public function check()
    {
        $defaultRouteConf = include $this->defaultRouteConfPath;
        $router = new Router($defaultRouteConf);

        UrlRule::loadRules($this->urlRulesFilePath);

        $urlRules = UrlRule::getRules();
        if(empty($urlRules)) {
            echo 'no rules need to check' . PHP_EOL;
        }

        $swooleHttpRequest = new SwooleHttpRequest();
        foreach($urlRules as $rule) {
            if(!isset($rule['unit_test']) or empty($rule['unit_test'])) {
                $this->checkResult = self::CHECK_FAILED;
                echo "rule : {$rule['regex']} check failed, reason : no unit_test" . PHP_EOL;
                break;
            }
            foreach($rule['unit_test'] as $testCase) {
                $swooleHttpRequest->server = [
                    'request_uri' => $testCase['request_uri'],
                ];
                $request = Request::createFromSwooleHttpRequest($swooleHttpRequest);
                $router->route($request);

                if($request->getRoute() !== $testCase['route']) {
                    $this->checkResult = self::CHECK_FAILED;
                    echo "rule : {$rule['regex']}, testcase : {$testCase['route']} check failed, reason : route pase faild" . PHP_EOL;
                    break 2;
                }
                if($router->getParameters() !== $testCase['parameters']) {
                    $this->checkResult = self::CHECK_FAILED;
                    echo "rule : {$rule['regex']} , testcase : {$testCase['route']} check failed, reason : parameters pase faild" . PHP_EOL;
                }
            }
        }

        if(self::CHECK_FAILED === $this->checkResult) {
            echo "route self check failed!";exit;
        }

        echo "route self check success!";
    }
}