<?php

namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Core\Config;
use Kdt\Iron\Nova\Nova;

class ServerRegisterInitiator
{
    use Singleton;

    CONST ENABLE_REGISTER = 1;
    CONST DISABLE_REGISTER = 0;

    private $register;

    public function __construct()
    {
        $this->register =  Config::get('registry.enable', self::ENABLE_REGISTER);
    }

    public function enableRegister()
    {
        $this->register = self::ENABLE_REGISTER;
    }

    public function disableRegister()
    {
        $this->register = self::DISABLE_REGISTER;
    }

    public function getRegister()
    {
        return $this->register;
    }

    public function init()
    {
        if (!$this->register) {
            return;
        }

        $configs = $this->createRegisterConfigs();
        foreach ($configs as $config) {
            $register = new ServerRegister();
            $coroutine = $register->register($config);
            Task::execute($coroutine);
        }
    }

    public function createRegisterConfigs()
    {
        $configs = [];
        $keys = Nova::getEtcdKeyList();
        foreach ($keys as list($protocol, $domain, $appName)) {
            $config = [];
            $config["services"] = Nova::getAvailableService($protocol, $domain, $appName);
            $config["domain"] = $domain;
            $config["appName"] = $appName;
            $config["protocol"] = $protocol;
            $configs[] = $config;
        }
        return $configs;
    }
}