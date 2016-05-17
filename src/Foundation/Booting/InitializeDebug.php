<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/15
 * Time: 下午10:48
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;

class InitializeDebug implements Bootable
{
    public function bootstrap(Application $app)
    {
        Debug::detect();
        Config::set('debug', Debug::get());
    }
} 