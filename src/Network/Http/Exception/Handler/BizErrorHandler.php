<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 12:09
 */

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Exception\BusinessException;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Network\Http\Response\Response;

class BizErrorHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        $errMsg = $e->getMessage();
        $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
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
}
