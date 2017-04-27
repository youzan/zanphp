<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/20
 * Time: 上午1:14
 */

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Sdk\Trace\ChromeTrace;
use Zan\Framework\Utilities\DesignPattern\Context;

class ChromeTraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        if (Debug::get() && Config::get("monitor.chrome_trace.run")) {
            $trace = new ChromeTrace();
            $context->set('chrome_trace', $trace);

            $rpcCtx = $context->get(RpcContext::KEY);
            if ($rpcCtx instanceof RpcContext) {
                $rpcCtx->set(ChromeTrace::TRANS_KEY, $trace->getJSONObject());
            }
        }
    }
}