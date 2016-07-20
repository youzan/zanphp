<?php

namespace Zan\Framework\Foundation\Booting;

use ErrorException;
use Exception;
use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;

class HandleExceptions implements Bootable
{
    /**
     * @var \Zan\Framework\Foundation\Application
     */
    private $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application  $app
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

    }

    private function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    private function handleException(Exception $e)
    {
        // But since PHP 7, $e is instance of Throwable
    }

    private function handleShutdown()
    {

    }
}
