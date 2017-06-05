<?php

return [
    'default_write' => [
        'engine'=> 'redis',
        'host' => '127.0.0.1',
        'port' => 6379,
        'pool'  => [
            'maximum-connection-count' => 50,
            'minimum-connection-count' => 10,
            'keeping-sleep-time' => 10,
            'init-connection'=> 10,
        ],
    ]
];
