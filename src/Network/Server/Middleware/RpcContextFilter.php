<?php

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Tcp\Request as TcpRequest;
use Zan\Framework\Network\Http\Request\Request as HttpRequest;


class RpcContextFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        /** @var RpcContext $rpcCtx */
        $rpcCtx = null;
        if ($request instanceof TcpRequest) {
            $rpcCtx = $request->getRpcContext();
        } else if ($request instanceof HttpRequest) {
            $rpcCtx = new RpcContext();
        }

        if ($rpcCtx) {
            $context->merge($rpcCtx->get(), false);
            $context->set("rpc-context", $rpcCtx);
        }
    }
}