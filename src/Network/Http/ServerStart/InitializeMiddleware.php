<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Server\Middleware\MiddlewareInitiator;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\ConfigLoader;

class InitializeMiddleware
{
    private $zanFilters = [
        \Zan\Framework\Network\Http\Middleware\SessionFilter::class,
    ];

    private $zanTerminators = [
        \Zan\Framework\Network\Http\Middleware\SessionTerminator::class,
    ];

    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $middlewarePath = Config::get('path.middleware');
        if (!is_dir($middlewarePath)) {
            return;
        }

        $middlewareInitiator = MiddlewareInitiator::getInstance();
        $middlewareConfig = ConfigLoader::getInstance()->load($middlewarePath);
        $exceptionHandlerConfig = isset($middlewareConfig['exceptionHandler']) ? $middlewareConfig['exceptionHandler'] : [];
        $exceptionHandlerConfig = is_array($exceptionHandlerConfig) ? $exceptionHandlerConfig : [];
        $middlewareConfig = isset($middlewareConfig['middleware']) ? $middlewareConfig['middleware'] : [];
        $middlewareConfig = is_array($middlewareConfig) ? $middlewareConfig : [];
        $middlewareInitiator->initConfig($middlewareConfig);
        $middlewareInitiator->initExceptionHandlerConfig($exceptionHandlerConfig);
        $middlewareInitiator->initZanFilters($this->zanFilters);
        $middlewareInitiator->initZanTerminators($this->zanTerminators);
    }
}
