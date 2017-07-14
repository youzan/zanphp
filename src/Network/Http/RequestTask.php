<?php

namespace Zan\Framework\Network\Http;

use swoole_http_response as SwooleHttpResponse;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Response\BaseResponse;
use Zan\Framework\Network\Http\Response\InternalErrorResponse;
use Zan\Framework\Network\Http\Response\ResponseTrait;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestTask
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var SwooleHttpResponse
     */
    private $swooleResponse;
    /**
     * @var Context
     */
    private $context;

    private $middleWareManager;

    public function __construct(Request $request, SwooleHttpResponse $swooleResponse, Context $context, MiddlewareManager $middlewareManager)
    {
        $this->request = $request;
        $this->swooleResponse = $swooleResponse;
        $this->context = $context;
        $this->middleWareManager = $middlewareManager;
    }

    public function run()
    {
        try {
            yield $this->doRun();
            return;
        } catch (\Throwable $t) {
            $e = t2ex($t);
        } catch (\Exception $e) {
        } finally {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }

        if (Debug::get()) {
            /** @noinspection PhpUndefinedVariableInspection */
            echo_exception($e);
        }
        $coroutine = $this->middleWareManager->handleHttpException($e);
        Task::execute($coroutine, $this->context);
        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }

    public function doRun()
    {
        $response = (yield $this->middleWareManager->executeFilters());
        if (null !== $response) {
            $this->context->set('response', $response);
            /** @var ResponseTrait $response */
            yield $response->sendBy($this->swooleResponse);
            $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
            return;
        }

        $dispatcher = Di::make(Dispatcher::class);
        $response = (yield $dispatcher->dispatch($this->request, $this->context));
        if (null === $response) {
            $code = BaseResponse::HTTP_INTERNAL_SERVER_ERROR;
            $response = new InternalErrorResponse("网络错误($code)", $code);
        }

        yield $this->middleWareManager->executePostFilters($response);

        $this->context->set('response', $response);
        yield $response->sendBy($this->swooleResponse);

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }
}
