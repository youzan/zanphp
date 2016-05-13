<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/9
 * Time: 下午9:10
 */

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