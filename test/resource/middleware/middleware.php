<?php

use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Utilities\Types\Arr;

class genericServiceFilter implements RequestFilter
{
    /**
     * @param \Zan\Framework\Contract\Network\Request $request
     * @param \Zan\Framework\Utilities\DesignPattern\Context $context
     * @return \Zan\Framework\Contract\Network\Response
     */
    public function doFilter(\Zan\Framework\Contract\Network\Request $request, \Zan\Framework\Utilities\DesignPattern\Context $context)
    {
        if ($request instanceof \Zan\Framework\Network\Tcp\Request) {
            $rpcCtx1 = (yield getRpcContext());

            /* @var \Zan\Framework\Network\Tcp\RpcContext $rpcContext */
            $rpcContext = $request->getRpcContext();

            if ($request->isGenericInvoke()) {
                $route = $request->getRoute();
                $rpcCtx = $request->getRpcContext();


//                var_dump($route);
//                var_dump($attachData);
//                var_dump($attachment);

                // 两种方式获取 卡门透传参数
                $kdtId = $context->get("kdt_id", -1);

                $kdtId = $rpcCtx->get($kdtId, 0);
                if ($kdtId === 42) {

                    // 抛出异常, 或者错误信息
//                    throw new \RuntimeException("invalid kdtid", 500);

//                    yield "invalid kdtId";
//                    return;
                }
            }
        }
        yield null;
    }

}

return [
    'match' => [
        // 可以单独针对 所有泛化调用 设置过滤器
        [
            "/com/youzan/nova/framework/generic/service/GenericService/invoke", "genericServiceFilterGroup",
        ],
        // 也可以直接 针对特定服务配置, 在过滤器内检测是否是泛化调用
        [
            "/Com/Youzan/Nova/Framework/Generic/Php/Service/GenericTestService/ThrowException", "genericServiceFilterGroup",
        ],
        [
            ".*", "all"
        ]
    ],
    'group' => [
        "genericServiceFilterGroup" => [
            genericServiceFilter::class
        ],
        "all" => [
            genericServiceFilter::class
        ],
    ]
];

