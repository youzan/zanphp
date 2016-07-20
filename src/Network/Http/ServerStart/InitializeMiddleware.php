<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/6
 * Time: 下午4:58
 */

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
        $middlewareInitiator = MiddlewareInitiator::getInstance();
        $middlewareConfig = ConfigLoader::getInstance()->load(Config::get('path.middleware'));
        $middlewareConfig = isset($middlewareConfig['middleware']) ? $middlewareConfig['middleware'] : [];
        $middlewareConfig = !is_array($middlewareConfig) || [] == $middlewareConfig ? [] : $middlewareConfig;
        $middlewareInitiator->initConfig($middlewareConfig);
        $middlewareInitiator->initZanFilters($this->zanFilters);
        $middlewareInitiator->initZanTerminators($this->zanTerminators);
    }
}