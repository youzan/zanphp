<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;

class Initator
{
    use InstanceManager;

    public function init(array $configs)
    {
        NovaConfig::getInstance()->setConfig($configs);
        Scanner::getInstance()->scan();
    }
}