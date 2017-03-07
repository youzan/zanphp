<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/31
 * Time: 下午5:15
 */
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