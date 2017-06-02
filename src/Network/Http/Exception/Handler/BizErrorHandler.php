<?php

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Exception\BusinessException;
use Zan\Framework\Foundation\View\JsVar;
use Zan\Framework\Foundation\View\View;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class BizErrorHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        $context = (yield getContextObject());
        $errorPage = $this->getErrorPage($e, $context);

        $code = $e->getCode();
        if (!BusinessException::isValidCode($code)) {
            yield false;
            return;
        }

        $request = (yield getContext('request'));
        if ($request->wantsJson()) {
            $context = [
                'code' => $code,
                'msg' => $e->getMessage(),
                'data' => '',
            ];
            yield new JsonResponse($context);
        } else {
            //html
            yield new Response($errorPage);
        }
    }

    private function getErrorPage(\Exception $e, Context $context)
    {
        $tpl = $this->parseConfig(get_class($e));
        if (!empty($tpl)) {
            $errorPage = $this->getTplErrorPage($tpl, $e, $context);
        } else {
            $errMsg = $e->getMessage();
            $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
            $errorPage = require $errorPagePath;
        }
        return $errorPage;
    }

    private function getTplErrorPage($tpl, \Exception $e, Context $context)
    {
        $jsVar = new JsVar();
        $env = $context->get('env', []);
        foreach ($env as $k => $v) {
            $jsVar->setBusiness($k, $v);
        }
        $csrfToken = $context->get('csrf_token', '');
        $jsVar->setCsrfToken($csrfToken);
        $viewData['exception'] = $e;
        $viewData['_js_var'] = $jsVar->get();

        $errorPage = View::display($tpl, $viewData);
        return $errorPage;
    }

    private function parseConfig($exceptionClassName)
    {
        $configMap = Config::get('biz_exception_error_page');
        if (!is_array($configMap) || empty($configMap)) {
            return [];
        }
        $configMap = array_change_key_case($configMap);
        $exceptionClassName = strtolower($exceptionClassName);

        if (empty($configMap)) {
            return [];
        }

        if (isset($configMap[$exceptionClassName])) {
            return $configMap[$exceptionClassName];
        }

        $prefix = [];
        $value = '';

        $parts = explode('\\', $exceptionClassName);
        foreach ($parts as $part) {
            if ($part) {
                $namespace = implode('\\', $prefix);
                $request = ltrim($namespace . '\\' . $part . '\\*', '\\');
                $wildcard = ltrim($namespace . '\\*', '\\');

                if (isset($configMap[$request])) {
                    $value = $configMap[$request];
                } else if (isset($configMap[$wildcard])) {
                    $value = $configMap[$wildcard];
                }

                $prefix[] = $part;
            }
        }
        return $value;
    }
}
