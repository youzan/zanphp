<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/9
 * Time: 下午4:43
 */

namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Http\Routing\Router;

class RouteInitiator
{
    use Singleton;

    public function init(array $config)
    {
        Router::getInstance()->setConfig($config);
    }
} 