<?php
namespace Zan\Framework\Network\MqSubscribe\WorkerStart;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\MqSubscribe\Subscribe\Checker;
use Zan\Framework\Network\MqSubscribe\Subscribe\Manager;

class InitializeMqSubscribe
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $config = Config::get('nsqConfig', []);
        Checker::handle($config);
        Manager::singleton()->init($config);
    }
} 