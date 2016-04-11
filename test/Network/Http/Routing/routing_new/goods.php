<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/5
 * Time: 下午8:31
 */
return [
    [
        'regex' => 'goods/:payType/:kdt_id/action',
        'rewrite' => 'goods/${payType}/action',
        'unit_test' =>
            [
                [
                    'request_uri' => 'goods/controller/123/action',
                    'route' => 'goods/controller/action',
                    'parameters' =>
                        [
                            'payType' => 'controller',
                            'kdt_id' => 123
                        ],
                ],
                [
                    'request_uri' => 'goods/controller/123/action',
                    'route' => 'goods/controller/action',
                    'parameter' =>
                        [
                            'payType' => 'controller',
                            'kdt_id' => 123
                        ],
                ],
            ],
    ],
];