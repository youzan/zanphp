<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 12:09
 */

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Exception\BusinessException;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Network\Http\Response\Response;

class BizErrorHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        $errMsg = $e->getMessage();
        $errorPagePath = $this->getErrorPagePath($e);
        $errorPage = require $errorPagePath;

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

    private function getErrorPagePath(\Exception $e)
    {
        $default = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
        $ref = new \ReflectionClass($e);
        $errorPage = $this->parseConfig($ref->getName());
        return (!empty($errorPage) && is_file($errorPage)) ? $errorPage : $default;
    }

    private function parseConfig($exceptionClassName)
    {
		$configMap = array_change_key_case(Config::get('exception_error_page'));
        $exceptionClassName = strtolower($exceptionClassName);

        if (empty($configMap)) {
			return [];
        }

        $parts = explode('\\', $exceptionClassName);
        if (isset($configMap[$exceptionClassName])) {
            return $configMap[$exceptionClassName];
        }

        $prefix = [];
        $exceptionPagePath = '';

        foreach ($parts as $part) {
            if ($part) {
                $namespace = implode('\\', $prefix);
                $request = ltrim($namespace . '\\' . $part . '\\*', '\\');
                $wildcard = ltrim($namespace . '\\*', '\\');

                if (isset($configMap[$request])) {
                    $exceptionPagePath = $configMap[$request];
                } else if (isset($configMap[$wildcard])) {
                    $exceptionPagePath = $configMap[$wildcard];
                }

                $prefix[] = $part;
            }
        }
		return $exceptionPagePath;
    }
}
