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
            Debug::setCliInput(true);
        } else if ($debug === 'false') {
            Debug::setCliInput(false);
        }

        $env = $input->getParameterOption('--env');
        if (!empty($env)) {
            RunMode::setCliInput($env);
        }

        $enableRegister = $input->getParameterOption('--enable-register');
        if ($enableRegister === 'true') {
            ServerRegisterInitiator::setCliInput(true);
        } else if ($enableRegister === 'false') {
            ServerRegisterInitiator::setCliInput(false);
        }
    }
}