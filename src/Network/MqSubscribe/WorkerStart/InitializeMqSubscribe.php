<?php
namespace Zan\Framework\Network\MqSubscribe\WorkerStart;


use Zan\Framework\Network\MqSubscribe\Subscribe\Checker;
use Zan\Framework\Network\MqSubscribe\Subscribe\Manager;

class InitializeMqSubscribe
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        $config = [
            'fenxiao_goods_index_create' => [
                'default' => [
                    'consumer' => 'xx',
                    'num' => 1
                ]
            ]
        ];
        
        Checker::handle($config);
        Manager::singleton()->init($config);
    }
} 