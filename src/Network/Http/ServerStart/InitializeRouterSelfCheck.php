<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Http\Routing\RouterSelfCheckInitiator;
use Zan\Framework\Foundation\Application;

class InitializeRouterSelfCheck
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        RouterSelfCheckInitiator::getInstance()->init();
    }
} 