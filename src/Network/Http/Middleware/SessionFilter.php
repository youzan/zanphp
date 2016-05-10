<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/9
 * Time: 下午6:29
 */

namespace Zan\Framework\Network\Http\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Network\Http\Session;
use Zan\Framework\Utilities\DesignPattern\Context;

class SessionFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        $session = new Session($request, $context->get('cookie'));
        $res = (yield $session->init());
        if ($res) {
            $context->set('session', $session);
        }

        yield null;
    }
}