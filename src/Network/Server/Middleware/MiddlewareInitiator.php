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

    public function init(array $config)
    {
        $config['match'] = isset($config['match']) ? $config['match'] : [];
        MiddlewareManager::getInstance()->setConfig($config);
    }
} 