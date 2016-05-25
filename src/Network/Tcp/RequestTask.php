<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestTask {
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
        $response = (yield $this->middleWareManager->executeFilters());
        if(null !== $response){
            $this->output($response);
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