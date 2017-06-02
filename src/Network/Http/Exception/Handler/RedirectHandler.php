<?php

namespace Zan\Framework\Network\Http\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Network\Http\Exception\RedirectException;
use Zan\Framework\Network\Http\Response\BaseResponse;
use Zan\Framework\Network\Http\Response\RedirectResponse;

class RedirectHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        if (!isset($e->redirectUrl) && !is_a($e, RedirectException::class)) {
            return null;
        }

        return RedirectResponse::create($e->redirectUrl, BaseResponse::HTTP_FOUND);
    }
}
