<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/16
 * Time: 21:40
 */

namespace Zan\Framework\Foundation\Booting;


use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\RunMode;

class InitializeRunMode implements Bootable
{
    public function bootstrap(Application $app)
    {
        RunMode::detect();
    }
}