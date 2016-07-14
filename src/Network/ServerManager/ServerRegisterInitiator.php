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

    const ENABLE_REGISTER  = 1;

    private static $cliInput = null;

    public static function setCliInput($mode)
    {
        self::$cliInput = $mode ? true : false;
    }

    public function init()
    {
        //TODO: check config position
        $config['services'] = Nova::getAvailableService();
        $haunt = Config::get('haunt.register');
        $enableRegister = isset($haunt['enable_register']) ? $haunt['enable_register'] : self::ENABLE_REGISTER;

        if (null !== self::$cliInput) {
            $enableRegister = self::$cliInput ? 1 : 0;
        }

        if (0 === $enableRegister) {
            return;
        }

        $register = new ServerRegister();
        $coroutine = $register->register($config);
        Task::execute($coroutine);
    }

}