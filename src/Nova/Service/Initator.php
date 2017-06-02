<?php

namespace Zan\Framework\Nova\Service;


use Zan\Framework\Nova\Foundation\Traits\InstanceManager;

class Initator
{
    use InstanceManager;

    public function init(array $configs)
    {
        NovaConfig::getInstance()->setConfig($configs);
        Scanner::getInstance()->scan();
    }
}