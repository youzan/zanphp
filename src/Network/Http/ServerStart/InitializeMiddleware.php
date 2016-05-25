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

class InitializeMiddleware
{
    private $extendFilters = [
        //'filter1', 'filter2'
    ];

    private $extendTerminators = [
         //'terminator1', 'terminator2'
    ];

    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $middlewareInitiator = MiddlewareInitiator::getInstance();
        $middlewareConfig = Config::get('middleware');
        $middlewareConfig = !is_array($middlewareConfig) || [] == $middlewareConfig ? [] : $middlewareConfig;
        $middlewareInitiator->initConfig($middlewareConfig);
        $middlewareInitiator->initExtendFilters($this->extendFilters);
        $middlewareInitiator->initExtendTerminators($this->extendTerminators);
    }
}