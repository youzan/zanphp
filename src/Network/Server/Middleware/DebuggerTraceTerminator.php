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
use Zan\Framework\Sdk\Trace\DebuggerTrace;
use Zan\Framework\Utilities\DesignPattern\Context;

class DebuggerTraceTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        /** @var DebuggerTrace $trace */
        $trace = $context->get('debugger_trace');
        if ($trace instanceof DebuggerTrace) {
            if (method_exists($response, "getException")) {
                $exception = $response->getException();
                if ($exception) {
                    $trace->commit("error", $exception);
                } else {
                    $trace->commit("info");
                }
            } else {
                $trace->commit("info");
            }

            $trace->report();
        }
    }
}