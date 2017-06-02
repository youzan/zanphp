<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;

class InitializeSharedObjects implements Bootable
{
    /**
     * @var \Zan\Framework\Foundation\Application
     */
    private $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        $this->initDiFacade();
    }

    private function initDiFacade()
    {
        Di::resolveFacadeInstance($this->app->getContainer());
    }

}