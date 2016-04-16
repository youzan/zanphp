<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/16
 * Time: 下午2:37
 */

namespace Zan\Framework\Network\Http\WorkStart;

use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Foundation\Core\Config;

class InitializeConnectionPoll
{
    /**
     * @param
     */
    public function bootstrap()
    {
        $config = Config::get('connection');
        ConnectionInitiator::getInstance()->init($config);
    }
}