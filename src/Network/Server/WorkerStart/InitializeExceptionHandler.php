<?php

namespace Zan\Framework\Network\Server\WorkerStart;

use ErrorException;
use Exception;
use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Foundation\Core\Debug;

class InitializeExceptionHandler implements Bootable
{

    public function bootstrap($server)
    {
        if (Debug::get()) {
            set_error_handler([self::class, 'handleError'], E_ALL & ~E_DEPRECATED);
        } else {
            set_error_handler([self::class, 'handleError'], E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
        }

        // set_exception_handler([$this, 'handleException']);
        // 需要使用显式使用swoole_event_wait()才能保证register_shutdown_function顺序
        // register_shutdown_function([$this, '...']);
    }

    public static function handleError($code, $message, $file, $line) {
        $context = "catched an error! errno: $code, message: $message, file: $file:$line";
        sys_echo($context);
        throw new ErrorException($context, $code);
    }

    private function handleException(Exception $e)
    {
        // But since PHP 7, $e is instance of Throwable
    }
}
