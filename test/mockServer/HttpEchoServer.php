<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/26
 * Time: 下午7:07
 */
$httpServer = new swoole_http_server("0.0.0.0", 12345);
$httpServer->on("request", function ($request, $response) {
    $get = $request->get;
    $msg = "";
    if(!is_null($get))
        $msg = http_build_query($get);
    $response->end($msg);
});

$httpServer->start();