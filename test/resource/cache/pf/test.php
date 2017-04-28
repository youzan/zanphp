<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/29
 * Time: 下午1:26
 */
return [
    'common'           => [
        'connection'    => 'redis.default_write'
    ],
    'test' => [
        'key' => 'test_abc_%s_%s',
        'exp' => 10
    ],
];