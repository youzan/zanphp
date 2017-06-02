<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\ConfigLoader;
use Zan\Framework\Network\Http\RequestExceptionHandlerChain;

class InitializeExceptionHandlerChain
{
    /**
     * @param \Zan\Framework\Network\Http\Server $server
     */
    public function bootstrap($server)
    {
        RequestExceptionHandlerChain::getInstance()->init();
    }
}
