<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/4/5
 * Time: 下午4:53
 */
return [
    'default_write' => [
        'engine'=> 'mysqli',
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => 'admin',
        'database' => 'zanTest',
        'port' => '3306',
        'pool'  => [
            'maximum-connection-count' => 50,
            'minimum-connection-count' => 0,
            'heartbeat-time' => 35000,
            'init-connection'=> 2,
        ],
    ],

];
