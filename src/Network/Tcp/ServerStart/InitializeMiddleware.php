<?php

namespace Zan\Framework\Network\Tcp\ServerStart;

use Zan\Framework\Network\Server\Middleware\MiddlewareInitiator;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\ConfigLoader;

class InitializeMiddleware
{
    private $zanFilters = [];

    private $zanTerminators = [];

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
        $configs = ConfigLoader::getInstance()->load($middlewarePath);
        $middlewareConfig = isset($configs['middleware']) ? $configs['middleware'] : [];
        $middlewareConfig = is_array($middlewareConfig) ? $middlewareConfig : [];
        $middlewareInitiator->initConfig($middlewareConfig);
        $exceptionHandlerConfig = isset($configs['exceptionHandler']) ? $configs['exceptionHandler'] : [];
        $exceptionHandlerConfig = is_array($exceptionHandlerConfig) ? $exceptionHandlerConfig : [];
        $middlewareInitiator->initExceptionHandlerConfig($exceptionHandlerConfig);
        $middlewareInitiator->initZanFilters($this->zanFilters);
        $middlewareInitiator->initZanTerminators($this->zanTerminators);
    }
}
