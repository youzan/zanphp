<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/4/10
 * Time: 下午1:08
 */
#!/usr/bin/env php

$server = new swoole_server('0.0.0.0', 5140);

$server->on("receive", function ($server, $fd, $from_id, $data) {
});

$server->start();