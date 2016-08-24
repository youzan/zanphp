<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/8/21
 * Time: 下午3:25
 */

namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class AsyncTaskTerminator implements RequestTerminator
{

    public function terminate(Request $request, Response $response, Context $context)
    {
        $callbacks = $context->get('async_task_queue');
        if (empty($callbacks)) {
            yield null;
            return;
        }
        for ($i = 0, $l = count($callbacks); $i < $l; $i++) {
            yield call_user_func($callbacks[$i]);
        }
        $context->set('async_task_queue', []);
        yield null;
    }
}
