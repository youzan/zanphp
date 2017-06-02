<?php
namespace Zan\Framework\Network\Server\WorkerStart;

use Zan\Framework\Network\ServerManager\ServerDiscoveryInitiator;

class InitializeServerDiscovery
{
    /**
     * @param $server
     * @param $workerId
     */
    public function bootstrap($server, $workerId)
    {
        ServerDiscoveryInitiator::getInstance()->init($workerId);
    }
}