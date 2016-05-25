<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/10
 * Time: 17:32
 */

namespace Zan\Framework\Foundation\Exception;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Foundation\Exception\Handler\ExceptionLogger;
use Zan\Framework\Network\Http\Response\BaseResponse;
use swoole_http_response as SwooleHttpResponse;

class ExceptionHandlerChain
{
    protected $handlerChain = [
        ExceptionLogger::class,
    ];
    protected $handlerMap = [];

    public function __construct()
    {
        $this->clearHandlers();
    }

    public function clearHandlers()
    {
        $this->handlerChain = [];
        $this->handlerMap = [];
    }

    public function handle(\Exception $e)
    {
        if (empty($this->handlerChain)) {
            //@TODO 输出到console
            return;
            // throw $e;
        }

        //at less one handler handle the exception
        //else throw the exception out
        $exceptionHandled = false;
        foreach ($this->handlerChain as $handler) {
            $response = (yield $handler->handle($e));
            if ($response) {
                $resp = (yield getContext('response'));
                if (!$resp) {
                    yield setContext('response', $response);
                }
                $exceptionHandled = true;
                break;
            }
        }
        
        if (is_a($response, BaseResponse::class)) {
            $swooleResponse = (yield getContext('swoole_response'));
            yield $response->sendBy($swooleResponse);
            return;
        }

        if (false === $exceptionHandled) {
            //@TODO 输出到console
            return;
            // throw $e;
        }
        yield null;
    }

    public function addHandler(ExceptionHandler $handler)
    {
        $hash = spl_object_hash($handler);
        if (isset($this->handlerMap[$hash])) {
            return false;
        }

        $this->handlerMap[$hash] = true;
        $this->handlerChain[] = $handler;
    }

    public function addHandlerByName($handlerName)
    {
        if (isset($this->handlerMap[$handlerName])) {
            return false;
        }

        $this->handlerMap[$handlerName] = true;
        $this->handlerChain[] = new $handlerName();
    }

    public function addHandlersByName(array $handlers)
    {
        if (!$handlers) {
            return false;
        }

        foreach ($handlers as $handlerName) {
            $this->addHandlerByName($handlerName);
        }
    }

}
