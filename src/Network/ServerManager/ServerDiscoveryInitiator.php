<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/31
 * Time: 下午5:17
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Network\ServerManager\ServerDiscovery;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class ServerDiscoveryInitiator
{
    use Singleton;

    public function init()
    {
        $serverDiscovery = new ServerDiscovery();
        $coroutine =  $serverDiscovery->start();
        while ($coroutine->valid()) {
            $coroutine->send();
        }
    }

}