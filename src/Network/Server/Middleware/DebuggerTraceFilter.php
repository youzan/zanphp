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
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\DebuggerTrace;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Http\Request\Request as HttpRequest;
use Zan\Framework\Network\Tcp\Request as TcpRequest;

class DebuggerTraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        if (Debug::get()) {
            $ctx = [];
            $type = null;
            $req = [];
            if ($request instanceof HttpRequest) {
                $ctx = $request->headers->all();
                $type = Constant::HTTP;
                $req = [
                    "method" => $request->getMethod(),
                    "uri" => $request->getRequestUri(),
                    "get" => $request->request->all(),
                    "post" => $request->query->all(),
                    "cookie" => $request->cookies->all(),
                ];
            } else if ($request instanceof TcpRequest) {
                $ctx = $request->getRpcContext()->get();
                $type = Constant::NOVA;
                $req = [
                    "service" => $request->getGenericServiceName(),
                    "method" => $request->getMethodName(),
                    "args" => $request->getArgs(),
                    "remote_ip" => $request->getRemoteIp(),
                    "remote_port" => $request->getRemotePort(),
                    "seq" => $request->getSeqNo(),
                ];
            }

            $trace = DebuggerTrace::fromCtx($ctx);
            $trace->beginTransaction($type, $req);

            $context->set("debugger_trace", $trace);
        }
    }
}