<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/9
 * Time: 下午6:29
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Utilities\DesignPattern\Context;

class TraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        var_dump("hahahaha");
    }
}