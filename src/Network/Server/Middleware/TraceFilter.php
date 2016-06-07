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

class TraceFilter implements RequestFilter
{
    public function doFilter(Request $request, Context $context)
    {
        $config = Config::get('monitor.trace');

        $rootId = $parentId = 'null';
        $isTcp = method_exists($request, 'getAttachData');
        $name = '';
        $eventId = null;
        if ($isTcp) {
            $attachData = $request->getAttachData();
            $attachArr = json_decode($attachData, true);
            if (isset($attachArr[Trace::TRACE_KEY]['rootId'])) {
                $rootId = $attachArr[Trace::TRACE_KEY]['rootId'];
            }

            if (isset($attachArr[Trace::TRACE_KEY]['parentId'])) {
                $parentId = $attachArr[Trace::TRACE_KEY]['parentId'];
            }

            if (isset($attachArr[Trace::TRACE_KEY]['eventId'])) {
                $eventId = $attachArr[Trace::TRACE_KEY]['eventId'];
            }
            $name = $request->getServiceName() . '.' . $request->getMethodName();
            $type = Constant::NOVA;
        } else {
            $type = Constant::HTTP;
            $name = $request->getUrl();
        }
        $trace = new Trace($config, $rootId, $parentId);
        $trace->initHeader($eventId);
        $trace->transactionBegin($type, $name);
        
        $context->set('trace', $trace);
    }
}