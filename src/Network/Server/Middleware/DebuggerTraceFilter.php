<?php

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Sdk\Trace\DebuggerTrace;
use Zan\Framework\Utilities\DesignPattern\Context;

class DebuggerTraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        if (Debug::get()) {
            DebuggerTrace::make($request, $context);
        }
    }
}