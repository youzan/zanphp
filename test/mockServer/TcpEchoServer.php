<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/26
 * Time: 下午2:04
 */
$server = new swoole_server('0.0.0.0', 2380);

$server->on("receive", function ($server, $fd, $from_id, $data) {
    $server->send($fd, $data);
});

$server->start();