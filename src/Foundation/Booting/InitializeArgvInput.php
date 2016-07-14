<?php

namespace Zan\Framework\Foundation\Booting;


use Symfony\Component\Console\Input\ArgvInput;
use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\RunMode;

class InitializeArgvInput implements Bootable
{
    public function bootstrap(Application $app)
    {
        $input = new ArgvInput();
        $debug = $input->getParameterOption('--debug');
        if ($debug === 'true') {
            Config::set('debug', true);
        } else if ($debug === 'false') {
            Config::set('debug', false);
        }

        $env = $input->getParameterOption('--env');
        if (!empty($env)) {
            RunMode::set($env);
            var_dump($env);
        }

        $enableRegister = $input->getParameterOption('--enable-register');
        if ($enableRegister === 'true') {

        } else if ($enableRegister === 'false') {

        }
    }
}