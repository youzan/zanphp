<?php


namespace Zan\Framework\Network\Http\Exception\Handler;


use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Network\Http\Response\JsonResponse;
use Zan\Framework\Network\Http\Response\Response;
use Zan\Framework\Network\Http\Security\Csrf\Exception\TokenException;

class ForbiddenHandler implements ExceptionHandler
{

    /**
     * @param \Exception $e
     *  * \Thrift\Exception\TException
     *  * \Zan\Framework\Foundation\Exception\ZanException
     *      * \Zan\Framework\Foundation\Exception\SystemException
     *      * \Zan\Framework\Foundation\Exception\BusinessException
     *      * OtherZanExceptions
     *  * OtherExceptions
     *
     * @return mixed
     *  * bool
     */
    public function handle(\Exception $e)
    {
        if ($e instanceof TokenException) {
            $errMsg = '禁止访问';
            $errorPagePath = Path::getRootPath() . '/vendor/zanphp/zan/src/Foundation/View/Pages/Error.php';
            $errorPage = require $errorPagePath;

            $request = (yield getContext('request'));
            if ($request->wantsJson()) {
                $context = [
                    'code' => $e->getCode(),
                    'msg' => $e->getMessage(),
                    'data' => '',
                ];
                yield new JsonResponse($context);
            } else {
                //html
                yield new Response($errorPage, Response::HTTP_FORBIDDEN);
            }
        }
    }
}