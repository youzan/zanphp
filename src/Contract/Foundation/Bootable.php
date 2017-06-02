<?php

namespace Zan\Framework\Contract\Foundation;

use Zan\Framework\Foundation\Application;


interface Bootable
{
    public function bootstrap(Application $app);
}