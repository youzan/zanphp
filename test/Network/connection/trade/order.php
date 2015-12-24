<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:22
 */
return [
    'item'             => [
        'engine'        => 'mysql',
        'charset'       => 'utf8mb4',
        'persistent'    => true,
        'username'      => 'order_item',
        'password'      => '123456',
        'connection'    => 'mysql:host=192.168.66.202;dbname=test_koudaitong',
        'connect_timeout' => 10,
    ],
    'item_cache'       => [
        'engine'        => 'redis',
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'timeout'       => 10,
    ],

];