<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Utilities\Validation\Factory as ValidatorFactory;

class InitializeSharedObjects
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

        //$this->initializeValidator();
    }

    private function initializeValidator()
    {
        $instance = ValidatorFactory::getInstance();

        $this->app->getDi()->set('validator', $instance);
    }

}