<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\AppConfig;
use Zan\Framework\Foundation\Core\Config;

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
        AppConfig::init();
    }
}