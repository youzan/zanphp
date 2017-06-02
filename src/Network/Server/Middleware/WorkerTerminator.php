<?php

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Server\Monitor\Worker;

class WorkerTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        Worker::getInstance()->reactionRelease();
    }
}