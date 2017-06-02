<?php

namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Http\Routing\UrlRule;

class UrlRuleInitiator
{
    use Singleton;

    public function init()
    {
        UrlRule::getInstance()->loadRules();
    }
} 