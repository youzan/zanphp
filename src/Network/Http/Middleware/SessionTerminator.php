<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/10
 * Time: 上午9:35
 */

namespace Zan\Framework\Network\Http\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class SessionTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        $session = $context->get('session');
        if (!$session) {
            return;
        }

        yield $session->writeBack();
    }
}