<?php

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Utilities\DesignPattern\Context;
use ZanPHP\Container\Container;
use ZanPHP\Contracts\ServiceChain\ServiceChainer;
use Zan\Framework\Network\Tcp\Request as TcpRequest;
use Zan\Framework\Network\Http\Request\Request as HttpRequest;

class ServiceChainFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        $container = Container::getInstance();

        if ($container->has(ServiceChainer::class)) {

            $chainValue = null;

            /** @var ServiceChainer $serviceChain */
            $serviceChain = $container->make(ServiceChainer::class);

            /** @var RpcContext $rpcCtx */
            $rpcCtx = $context->get("rpc-context");

            if ($request instanceof TcpRequest) {
                $chainValue = $serviceChain->getChainValue(ServiceChainer::TYPE_TCP, $rpcCtx->get());
            } else if ($request instanceof HttpRequest) {
                $swooleRequest = $context->get("swoole_request");
                $chainValue = $serviceChain->getChainValue(ServiceChainer::TYPE_HTTP, $swooleRequest->header);
            }

            if ($chainValue === null && getenv("ZAN_JOB_MODE")) {
                $chainValue = $serviceChain->getChainValue(ServiceChainer::TYPE_JOB);
            }

            if ($chainValue !== null) {
                $jsonValue = json_encode(["name" => $chainValue]);

                $novaKey = $serviceChain->getChainKey(ServiceChainer::TYPE_TCP);
                $httpKey = $serviceChain->getChainKey(ServiceChainer::TYPE_HTTP);

                $rpcCtx->set($novaKey, $jsonValue);
                $rpcCtx->set($httpKey, $jsonValue);

                $context->set("service-chain", $serviceChain);
                $context->set("service-chain-value", $chainValue);
            }
        }
    }
}