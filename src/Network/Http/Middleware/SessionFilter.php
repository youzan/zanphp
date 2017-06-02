<?php

namespace Zan\Framework\Network\Http\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
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