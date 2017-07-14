<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use ZanPHP\Container\Container;

class InitializeContainer implements Bootable
{

    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $binds = Config::get("zan_container", []);
        $container = Container::getInstance();
        foreach ($binds as $abstract => $bindArgs) {
            $container->bind($abstract, ...(array)$bindArgs);
        }
    }
}