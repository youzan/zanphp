<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/19
 * Time: 上午10:19
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;

class InitializeEnv implements Bootable
{
    public function bootstrap(Application $app)
    {
        ini_set('memory_limit', '2000M');
    }
} 