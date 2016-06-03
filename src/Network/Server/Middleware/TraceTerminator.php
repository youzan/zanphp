<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/10
 * Time: 上午9:35
 */

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Utilities\DesignPattern\Context;

class TraceTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        $trace = $context->get('trace');
        $exception = $response->getException();
        if ($exception) {
            $trace->commit($exception);
        } else {
            $trace->commit(Constant::SUCCESS);
        }

        //send数据
    }
}