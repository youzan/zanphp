<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Thrift\Exception\TApplicationException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Server\Monitor\Worker;
use Zan\Framework\Sdk\Monitor\Hawk;
use Zan\Framework\Sdk\Trace\Constant;
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
        try {
            yield $this->doRun();
        } catch (\Exception $e) {
            $hawk = Hawk::getInstance();
            if ($e instanceof TApplicationException) {
                $hawk->addTotalFailureTime(Hawk::SERVER,
                    $this->request->getServiceName(),
                    $this->request->getMethodName(),
                    $this->request->getRemoteIp(),
                    microtime(true) - $this->request->getStartTime());
                $hawk->addTotalFailureCount(Hawk::SERVER,
                    $this->request->getServiceName(),
                    $this->request->getMethodName(),
                    $this->request->getRemoteIp());
            } else {
                $hawk->addTotalSuccessTime(Hawk::SERVER,
                    $this->request->getServiceName(),
                    $this->request->getMethodName(),
                    $this->request->getRemoteIp(),
                    microtime(true) - $this->request->getStartTime());
                $hawk->addTotalSuccessCount(Hawk::SERVER,
                    $this->request->getServiceName(),
                    $this->request->getMethodName(),
                    $this->request->getRemoteIp());
            }
            $this->response->sendException($e);
            $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
        }
    }
    
    private function doRun()
    {
        $response = (yield $this->middleWareManager->executeFilters());
        if(null !== $response){
            $this->output($response);
            return;
        }

        $dispatcher = new Dispatcher();
        $trace = $this->context->get('trace');
        $trace->logEvent(Constant::NOVA_PROCCESS, Constant::SUCCESS, 'dispatch');
        $result = (yield $dispatcher->dispatch($this->request, $this->context));
        $this->output($result);

        $hawk = Hawk::getInstance();
        $hawk->addTotalSuccessTime(Hawk::SERVER,
            $this->request->getServiceName(),
            $this->request->getMethodName(),
            $this->request->getRemoteIp(),
            microtime(true) - $this->request->getStartTime());
        $hawk->addTotalSuccessCount(Hawk::SERVER,
            $this->request->getServiceName(),
            $this->request->getMethodName(),
            $this->request->getRemoteIp());

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }

    private function output($execResult)
    {
        return $this->response->end($execResult);
    }


}