<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\AppConfig;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\IronConfig;
use Zan\Framework\Foundation\Core\MultiConfig;
use Zan\Framework\Utilities\Types\Arr;

class LoadConfiguration implements Bootable
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        date_default_timezone_set('Asia/Shanghai');
        mb_internal_encoding('UTF-8');

        Config::init();
        IronConfig::init();
        AppConfig::init();
        MultiConfig::init();

        $this->mixRegistryConfig();
    }

    private function mixRegistryConfig()
    {
        $registry = Config::get("registry", []);
        $haunt = Config::get("haunt", []);
        $nova = Config::get("nova", []);
        $defaultEtcdNodes = Config::get("zan_registry", []);

        $mixed = Arr::merge($defaultEtcdNodes, $nova, $haunt, $registry);
        $mixed["haunt"] = $haunt;
        Config::set("registry", $mixed);
    }
}