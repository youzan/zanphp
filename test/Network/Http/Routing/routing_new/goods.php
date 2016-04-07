<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午8:31
 */
return [
    'goods/:payType/:kdt_id/xxx' => [
        'rewrite' => 'goods',
        'example' => [
            'goods/wxpay/xxx' => 'goods/wxpay/index',
            'goods/alipay/xxx' => 'goods/alipay/index',
        ],
    ],
];