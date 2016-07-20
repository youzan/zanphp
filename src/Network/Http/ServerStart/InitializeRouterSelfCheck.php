<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/9
 * Time: 下午9:10
 */

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Http\Routing\RouterSelfCheckInitiator;
use Zan\Framework\Foundation\Application;

class InitializeRouterSelfCheck
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        RouterSelfCheckInitiator::getInstance()->init();
    }
} 