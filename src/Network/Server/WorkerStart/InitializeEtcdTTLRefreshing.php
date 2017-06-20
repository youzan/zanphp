<?php

namespace Zan\Framework\Network\Server\WorkerStart;

use Zan\Framework\Contract\Network\Bootable;
use Zan\Framework\Network\ServerManager\ServerRegister;
use Zan\Framework\Network\ServerManager\ServerRegisterInitiator;

class InitializeEtcdTTLRefreshing implements Bootable
{
    public function bootstrap($server)
    {
        $workerId = func_get_arg(1);
        if ($workerId === 0) {
            $enableRegister = ServerRegisterInitiator::getInstance()->getRegister();
            if ($enableRegister) {
                $sr = ServerRegisterInitiator::getInstance();
                $serverRegister = new ServerRegister();

                $configs = $sr->createRegisterConfigs();
                foreach ($configs as $config) {
                    $serverRegister->refreshingEtcdV2TTL($config);
                }
            }
        }
    }
}