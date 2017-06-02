<?php

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Network\Http\Response\Response;

class ServerUnavailableHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        $code = $e->getCode();
        if ($code != 503) {
            yield false;
            return;
        }

        $errMsg = $e->getMessage();
        $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
        $errorPage = require $errorPagePath;


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
            yield new Response($errorPage, Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
