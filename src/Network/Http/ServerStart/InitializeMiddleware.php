<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/6
 * Time: 下午4:58
 */

namespace Zan\Framework\Network\Http\ServerStart;

use Zan\Framework\Network\Server\Middleware\MiddlewareInitiator;
use Zan\Framework\Foundation\Core\Config;

class InitializeMiddleware
{
    /**
     * @param $server
     */
    public function bootstrap($server)
    {
        MiddlewareInitiator::getInstance()->init(Config::get('middleware'));
    }
} 