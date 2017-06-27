<?php

namespace Zan\Framework\Network\MqSubscribe;

use Zan\Framework\Network\MqSubscribe\Subscribe\Manager;

/**
 * Class MqSubscribe
 * @package Zan\Framework\Network\MqSubscribe
 * 
 * Mq Subscribe服务启动入口
 */
class MqSubscribe
{
    public function start()
    {
        Manager::singleton()->start();
    }
} 