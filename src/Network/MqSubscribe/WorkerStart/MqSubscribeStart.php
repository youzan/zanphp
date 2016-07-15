<?php
namespace Zan\Framework\Network\MqSubscribe\WorkerStart;

use Zan\Framework\Network\MqSubscribe\Subscribe\Manager;

class MqSubscribeStart
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        Manager::singleton()->init()->start();
    }
} 