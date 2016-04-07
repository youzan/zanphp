<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午8:31
 */
return [
    'goods/:payType/:kdt_id/xxx' => [
        'rewrite' => 'goods/${payType}/xxx.json',
        'example' => [
            'goods/wxpay/xxx' => 'goods/wxpay/xxx',
            'goods/alipay/xxx' => 'goods/alipay/xxx',
        ],
    ],
];