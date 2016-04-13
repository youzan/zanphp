<?php

return [
    'dev' => [
        'php' => [
            'host' => 'api.koudaitong.com',
            'port' => 80,
            'timeout' => 2
        ],
        'java' => [
            'courier.push.sendMessage' => [
                'host' => '192.168.66.204',
                'port' => 8011,
                'timeout' => 3
            ]
        ],
    ],
    'pre' => [
        'php' => [
            'host' => 'api.koudaitong.com',
            'port' => 80,
            'timeout' => 2
        ],
        'java' => [
            'courier.push.sendMessage' => [
                'host' => '192.168.66.204',
                'port' => 8011,
                'timeout' => 3
            ]
        ],
    ],
    'online' => [
        'php' => [
            'host' => 'api.koudaitong.com',
            'port' => 80,
            'timeout' => 2
        ],
        'java' => [
            'courier.push.sendMessage' => [
                'host' => '10.200.175.192', //线上ip
                'port' => 8011,
                'timeout' => 3
            ]
        ],
    ],
];