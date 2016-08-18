<?php

namespace Zan\Framework\Foundation\Booting;


use Symfony\Component\Console\Input\ArgvInput;
use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\ServerManager\ServerRegisterInitiator;

class InitializeCliInput implements Bootable
{
    public function bootstrap(Application $app)
    {
        $input = new ArgvInput();
        $debug = $input->getParameterOption('--debug');
        if ($debug === 'true') {
            Debug::enableDebug();
        } else if ($debug === 'false') {
            Debug::disableDebug();
        }

        $env = $input->getParameterOption('--env');
        if (!empty($env)) {
            RunMode::set($env);
        }

        $serviceRegister = $input->getParameterOption('--service-register');
        if ($serviceRegister === 'true') {
            ServerRegisterInitiator::instance()->enableRegister();
        } else if ($serviceRegister === 'false') {
            ServerRegisterInitiator::instance()->disableRegister();
        }
    }
}