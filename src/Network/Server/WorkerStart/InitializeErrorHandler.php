<?php

namespace Zan\Framework\Network\Server\WorkerStart;

use ErrorException;
use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Foundation\Core\Debug;

class InitializeErrorHandler implements Bootable
{

    public function bootstrap($server)
    {
        if (Debug::get()) {
            set_error_handler([self::class, 'handleError'], E_ALL & ~E_DEPRECATED);
        } else {
            set_error_handler([self::class, 'handleError'], E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        }
    }

    public static function handleError($code, $message, $file, $line) {
        $context = "catched an error! errno: $code, message: $message, file: $file:$line";
        sys_echo($context);
        throw new ErrorException($context, $code);
    }
}
