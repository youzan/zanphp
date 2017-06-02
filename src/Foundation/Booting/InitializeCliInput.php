<?php

namespace Zan\Framework\Foundation\Booting;


use Symfony\Component\Console\Input\ArgvInput;
use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Core\RunMode;

class InitializeCliInput implements Bootable
{
    public function bootstrap(Application $app)
    {
        $input = new ArgvInput();
        if ($input->hasParameterOption('--help')) {
            $help = <<<EOF
Options:
--debug [true/false]              enable/disable debug mode
--env   [ENV]                     set run mode environment, eg: online, test, qatest
--service-register [true/false]   enable/disable service register

EOF;
            exit($help);
        }
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
    }
}