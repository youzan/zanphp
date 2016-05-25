<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午5:12
 */
return [
    [
        '/trade/:payType/xxx' => [
            'rewrite' => '${payType}/notify',
            'example' => [
                '/trade/wxpay/xxx' => '/wxpay/notify',
                '/trade/alipay/xxx' => '/alipay/notify',
            ],
        ],
        '/trade/:payType/xxx' => [
            'rewrite' => '${payType}/notify',
            'example' => [
                '/trade/wxpay/xxx' => '/wxpay/notify',
                '/trade/alipay/xxx' => '/alipay/notify',
            ],
        ],
        '/trade/.*/xxx' => [
            'rewrite' => '/trade/confirm',
            'example' => [
                '/trade/xxx/xxx' => '/trade/confirm',
                '/trade/yyy/xxx' => '/trade/confirm',
            ],
        ],
    ],
];