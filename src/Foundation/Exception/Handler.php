<?php
namespace Zan\Framework\Foundation\Exception;

use Zan\Framework\Foundation\Core\Debug;

class Handler {

    public static function initErrorHandler()
    {
        ini_set('display_errors', false);

        if (Debug::get()) {
            set_exception_handler(['Handler', 'handleException']);
        } else {
            set_exception_handler(['Handler', 'handleExceptionProduct']);
        }

        set_error_handler(['Handler', 'handleError']);
        register_shutdown_function(['Handler', 'handleFatalError']);
    }

    public static function handleException(\Exception $e)
    {
        throw new \Exception($e->getMessage());
    }

    public static function handleExceptionProduct(\Exception $e)
    {

    }

    public static function handleError($code, $message, $file, $line) {

    }

    public static function handleFatalError() {

    }
}