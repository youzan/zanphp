<?php

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class ClearContextTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        $context = (yield getContextObject());
        if ($context instanceof Context) {
            $context->clear();
        }
        unset($context);
        $context = null;
    }
}

