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
use Zan\Framework\Foundation\Core\Config;

use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;

class ServerDiscoveryInitiator
{
    use Singleton;

    public function init()
    {
        $config = Config::get('haunt');
        if (empty($config)) {
            throw new ServerConfigException();
        }
        foreach ($config['modules'] as $modules) {
            $serverDiscovery = new ServerDiscovery($config, $modules);
            $coroutine = $serverDiscovery->start();
            Task::execute($coroutine);
        }
    }

}