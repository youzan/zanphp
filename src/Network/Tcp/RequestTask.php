<?php

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Exception\GenericInvokeException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestTask
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Context
     */
    private $context;
    private $middleWareManager;


    public function __construct(Request $request, Response $response, Context $context, MiddlewareManager $middlewareManager)
    {
        $this->request = $request;
        $this->response = $response;
        $this->context = $context;
        $this->middleWareManager = $middlewareManager;
    }

    public function run()
    {
        try {
            yield $this->doRun();
        } catch (\Throwable $t) {
            $this->handleRequestException(t2ex($t));
        } catch (\Exception $e) {
            $this->handleRequestException($e);
        }
    }
    private function handleRequestException($e)
    {
        $coroutine = RequestHandler::handleException($this->middleWareManager, $this->response, $e);
        Task::execute($coroutine, $this->context);

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }
    
    private function doRun()
    {
        $response = (yield $this->middleWareManager->executeFilters());
        if(null !== $response){
            if ($this->request->isGenericInvoke()) {
                throw new GenericInvokeException(strval($response));
            } else {
                $this->output($response);
            }
            $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
            return;
        }

        $dispatcher = new Dispatcher();
        $result = (yield $dispatcher->dispatch($this->request, $this->context));
        $this->output($result);

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }

    private function output($execResult)
    {
        return $this->response->end($execResult);
    }
}