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
    public $checkResult;

    const CHECK_SUCCESS = 'success';
    const CHECK_FAILED = 'failed';
    const OUTPUT_PREFIX = '【RouteCheck】';

    public function __construct($urlRulesPath)
    {
        $this->urlRulesPath = $urlRulesPath;
        $this->checkResult = self::CHECK_SUCCESS;
    }

    public function check()
    {
        UrlRule::loadRules($this->urlRulesPath);
        $urlRules = UrlRule::getRules();
        if(empty($urlRules)) {
            echo 'no rules need to check' . PHP_EOL;
        }

        $router = new Router();
        $swooleHttpRequest = new SwooleHttpRequest();
        foreach($urlRules as $rule) {
            if(!isset($rule['unit_test']) or empty($rule['unit_test'])) {
                $this->checkResult = self::CHECK_FAILED;
                $msg = "rule : {$rule['regex']} check failed, reason : no unit_test";
                $this->output($msg);
                break;
            }
            foreach($rule['unit_test'] as $testCase) {
                $swooleHttpRequest->server = [
                    'request_uri' => $testCase['request_uri'],
                ];
                $request = Request::createFromSwooleHttpRequest($swooleHttpRequest);
                $router->route($request);

                if($request->getRoute() != $testCase['route']) {
                    $this->checkResult = self::CHECK_FAILED;
                    $msg = "rule : {$rule['regex']}, testcase : {$testCase['route']} check failed, reason : route parse failed";
                    $this->output($msg);
                    break 2;
                }
                if($router->getParameters() != $testCase['parameters']) {
                    $this->checkResult = self::CHECK_FAILED;
                    $msg = "rule : {$rule['regex']} , testcase : {$testCase['route']} check failed, reason : parameters parse failed";
                    $this->output($msg);
                }
            }
        }

        if(self::CHECK_FAILED === $this->checkResult) {
            $msg = 'route self check failed!';
            $this->output($msg);
            exit;
        }

        $msg = "route self check success!";
        $this->output($msg);
    }

    protected function output($msg)
    {
        echo self::OUTPUT_PREFIX . $msg . PHP_EOL;
    }
}