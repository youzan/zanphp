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
use Zan\Framework\Foundation\Coroutine\Task;

class ServerDiscoveryInitiator
{
    use Singleton;

    public function init()
    {
        $serverDiscovery = new ServerDiscovery();
        $coroutine =  $serverDiscovery->start();
        Task::execute($coroutine);
    }

}