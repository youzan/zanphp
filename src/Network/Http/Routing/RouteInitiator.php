<?php

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