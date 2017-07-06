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
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Utilities\DesignPattern\Context;

class TraceTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        /** @var Trace $trace */
        $trace = $context->get('trace');
        $traceHandle = $context->get('traceHandle');
        if (method_exists($response, 'getException')) {
            $exception = $response->getException();
            if ($exception) {
                $trace->commit($traceHandle, $exception);
            } else {
                $trace->commit($traceHandle, Constant::SUCCESS);
            }
        } else {
            $trace->commit($traceHandle, Constant::SUCCESS);
        }

        //send数据
        yield $trace->send();
    }
}