<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/6
 * Time: 下午4:26
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class MiddlewareInitiator
{
    use Singleton;

    public function initConfig(array $config = [])
    {
        $config['match'] = isset($config['match']) ? $config['match'] : [];
        MiddlewareConfig::getInstance()->setConfig($config);
    }

    public function initExtendFilters(array $extendFilters = [])
    {
        MiddlewareConfig::getInstance()->setExtendFilters($extendFilters);
    }

    public function initExtendTerminators(array $extendTerminators = [])
    {
        MiddlewareConfig::getInstance()->setExtendTerminators($extendTerminators);
    }
} 