<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/6/6
 * Time: 上午10:41
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\ServerManager\ServerRegister;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Kdt\Iron\Nova\Nova;

class ServerRegisterInitiator
{
    use Singleton;

    public function init()
    {
        $config = Config::get('nova.platform');
        $config['services'] = Nova::getAvailableService();

        $appName = Application::getInstance()->getName();
        $config['module'] = $appName;
        $register = new ServerRegister();
        $coroutine = $register->register($config);
        Task::execute($coroutine);
    }

}