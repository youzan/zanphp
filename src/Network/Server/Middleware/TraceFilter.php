<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/9
 * Time: 下午6:29
 */

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Tcp\Request as TcpRequest;
use Zan\Framework\Network\Http\Request\Request as HttpRequest;
use Zan\Framework\Network\WebSocket\Request as WebSocketRequest;

class TraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        $config = Config::get('monitor.trace');

        $rootId = $parentId = 'null';
        $name = '';
        $eventId = null;
        if ($request instanceof TcpRequest) {
            $attachArr = $request->getRpcContext()->get();
            if (isset($attachArr[Trace::TRACE_KEY]['rootId'])) {
                $rootId = $attachArr[Trace::TRACE_KEY]['rootId'];
            } else if (isset($attachArr[Trace::TRACE_KEY][Trace::ROOT_ID_KEY])) {
                $rootId = $attachArr[Trace::TRACE_KEY][Trace::ROOT_ID_KEY];
            }

            if (isset($attachArr[Trace::TRACE_KEY]['parentId'])) {
                $parentId = $attachArr[Trace::TRACE_KEY]['parentId'];
            } else if (isset($attachArr[Trace::TRACE_KEY][Trace::PARENT_ID_KEY])) {
                $parentId = $attachArr[Trace::TRACE_KEY][Trace::PARENT_ID_KEY];
            }

            if (isset($attachArr[Trace::TRACE_KEY]['eventId'])) {
                $eventId = $attachArr[Trace::TRACE_KEY]['eventId'];
            } else if (isset($attachArr[Trace::TRACE_KEY][Trace::CHILD_ID_KEY])) {
                $eventId = $attachArr[Trace::TRACE_KEY][Trace::CHILD_ID_KEY];
            }
            $name = $request->getServiceName() . '.' . $request->getMethodName();
            $type = Constant::NOVA_SERVER;
        } else if ($request instanceof HttpRequest) {
            $type = Constant::HTTP;
            $name = $request->getUrl();
        } else if ($request instanceof WebSocketRequest) {
            $type = Constant::WEBSOCKET;
            $name = $request->getRoute();
        } else {
            return;
        }
        $trace = new Trace($config, $rootId, $parentId);
        $trace->initHeader($eventId);
        $traceHandle = $trace->transactionBegin($type, $name);
        
        $context->set('trace', $trace);
        $context->set('traceHandle', $traceHandle);
    }
}