<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/5/8
 * Time: 下午10:14
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Server\Monitor\Worker;

class BaseTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        Worker::getInstance()->reactionRelease();
    }
}