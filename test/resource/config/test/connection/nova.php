<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/2/21
 * Time: 下午5:38
 */

return [
    'engine'=> 'novaClient',
    'timeout' => 15000,
    'persistent' => true,
    'heartbeat-time' => 15000,
    'load_balancing_strategy' => 'polling',
    'config'    => [
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,
        'open_nova_protocol' => 1
    ],
];
