<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/7/5
 * Time: 下午4:55
 */
namespace Zan\Framework\Network\Server\Middleware;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Contract\Network\Response;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Utilities\DesignPattern\Context;

class ConnectionManagerTerminator implements RequestTerminator
{
    public function terminate(Request $request, Response $response, Context $context)
    {
        $trace = $context->get('trace');
        if (method_exists($response, 'getException')) {
            $exception = $response->getException();
            if ($exception) {
                $trace->commit($exception);
            } else {
                $trace->commit(Constant::SUCCESS);
            }
        } else {
            $trace->commit(Constant::SUCCESS);
        }

        //send数据
        yield $trace->send();
    }
}