<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/4/15
 * Time: 下午2:09
 */

namespace Zan\Framework\Network\Http\ServerStart;

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
