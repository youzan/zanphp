<?php

namespace Zan\Framework\Foundation\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;

class ExceptionLogger extends BaseExceptionHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        if (!isset($e->logLevel)) {
            return false;
        }
        if (null === $e->logLevel) {
            return false;
        }

        //TODO: logging
    }
}
