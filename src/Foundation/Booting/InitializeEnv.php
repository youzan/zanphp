<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Env;

class InitializeEnv implements Bootable
{
    public function bootstrap(Application $app)
    {
        ini_set('memory_limit', '2000M');
        Env::init();
    }
} 