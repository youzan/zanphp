<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

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

    public function __construct(Request $request, Response $response, Context $context)
    {
        $this->request = $request;
        $this->response = $response;
        $this->context = $context;
    }

    public function run()
    {
        $dispatcher = new Dispatcher();
        $result = (yield $dispatcher->dispatch($this->request, $this->context));
        $this->output($result);
        Worker::instance()->reactionRelease();
    }

    private function output($execResult)
    {
        return $this->response->end($execResult);
    }


}