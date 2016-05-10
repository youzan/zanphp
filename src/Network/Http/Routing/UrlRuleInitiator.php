<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/9
 * Time: 下午5:29
 */

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