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
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Dir;

class RouterSelfCheck
{
    public $urlRulesPath = '';
    public $checkListPath = '';
    public $checkList = [];
    public $urlRules = [];
    public $checkResult;

    const CHECK_SUCCESS = 'success';
    const CHECK_FAILED = 'failed';
    const OUTPUT_PREFIX = '【RouteCheck】';

    public function __construct($urlRulesPath, $checkListPath)
    {
        $this->urlRulesPath = $urlRulesPath;
        $this->checkListPath = $checkListPath;
        $this->checkResult = self::CHECK_SUCCESS;
    }

    public function check()
    {
        $this->loadUrlRules();
        if(empty($this->urlRules)) {
            echo 'no rules need to check' . PHP_EOL;
        }
        $this->loadCheckList();
        $router = new Router();
        $swooleHttpRequest = new SwooleHttpRequest();
        foreach($this->urlRules as $rule) {
            if(!isset($this->checkList[$rule['regex']]) or empty($this->checkList[$rule['regex']])) {
                $this->checkResult = self::CHECK_FAILED;
                $msg = "rule : {$rule['regex']} check failed, reason : no unit_test";
                $this->output($msg);
                break;
            }
            foreach($this->checkList[$rule['regex']] as $testCase) {
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

    private function loadCheckList()
    {
        $checkListFiles = Dir::glob($this->checkListPath, '*.check.php');
        if (!$checkListFiles) return false;
        foreach ($checkListFiles as $file)
        {
            $checkList = include $file;
            if (!is_array($checkList)) continue;
            $this->checkList = Arr::merge($this->checkList, $checkList);
        }
    }

    private function loadUrlRules()
    {
        UrlRule::loadRules($this->urlRulesPath);
        $this->urlRules = UrlRule::getRules();
    }

    protected function output($msg)
    {
        //TODO: throw Exception
        echo self::OUTPUT_PREFIX . $msg . PHP_EOL;
    }
}