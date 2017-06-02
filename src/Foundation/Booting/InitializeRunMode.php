<?php

namespace Zan\Framework\Foundation\Booting;


use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\RunMode;

class InitializeRunMode implements Bootable
{
    public function bootstrap(Application $app)
    {
        RunMode::detect();
        $mode = RunMode::get();
        sys_echo("Running in $mode mode");
    }
}