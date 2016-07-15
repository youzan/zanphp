<?php
namespace Zan\Framework\Network\MqSubscribe;

use Zan\Framework\Network\MqSubscribe\Subscribe\Manager;

class MqSubscribe
{
    public function start()
    {
        Manager::singleton()->start();
    }
} 