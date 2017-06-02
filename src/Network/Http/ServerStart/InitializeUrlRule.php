<?php

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Http\Routing\UrlRuleInitiator;
use Zan\Framework\Foundation\Application;

class InitializeUrlRule
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        UrlRuleInitiator::getInstance()->init();
    }
} 