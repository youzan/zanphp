<?php

return [
    'syslog_default' => [
        'engine'=> 'syslog',
        'host' => '127.0.0.1',
        'port' => '5140',
        'timeout' => 3000,
        'persistent' => true,
        'pool'  => [
            'keeping-sleep-time' => 10000,
            'init-connection'=> 2,
        ],
    ],
];