<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午8:31
 */
return [
    'goods/:payType/:kdt_id/xxx' => [
        'rewrite' => 'goods/${payType}/notify/aaaa/bbbb/ccccc/dddd',
        'example' => [
            'goods/wxpay/xxx' => 'goods/wxpay/notify/aaaa/bbbb/ccccc/dddd',
            'goods/alipay/xxx' => 'goods/alipay/notify/aaaa/bbbb/ccccc/dddd',
        ],
    ],
];