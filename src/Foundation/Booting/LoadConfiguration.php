<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Foundation\Application;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application  $app
     */
    public function bootstrap(Application $app)
    {
        // TODO: 加载配置，检测环境


        date_default_timezone_set('Asia/Shanghai');

        mb_internal_encoding('UTF-8');
    }
}