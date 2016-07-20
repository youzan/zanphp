<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Utilities\Validation\Factory as ValidatorFactory;

class InitializeSharedObjects implements Bootable
{
    /**
     * @var \Zan\Framework\Foundation\Application
     */
    private $app;

    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application  $app
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        $this->initDiFacade();

        //$this->initializeValidator();
    }

    private function initializeValidator()
    {
        $instance = ValidatorFactory::getInstance();

        Di::set('validator', $instance);
    }

    private function initDiFacade()
    {
        Di::resolveFacadeInstance($this->app->getContainer());
    }

}