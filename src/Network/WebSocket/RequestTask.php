<?php
namespace Zan\Framework\Network\WebSocket;

use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Http\Dispatcher;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestTask
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Context
     */
    private $context;

    private $middleWareManager;

    public function __construct(Request $request, Context $context, MiddlewareManager $middlewareManager)
    {
        $this->request = $request;
        $this->context = $context;
        $this->middleWareManager = $middlewareManager;
    }

    public function run()
    {
        try {
            yield $this->doRun();
        } catch (\Throwable $t) {
            $e = t2ex($t);
        } catch (\Exception $e) {
        }

        if (isset($e)) {
            if (Debug::get()) {
                echo_exception($e);
            }
            $coroutine = RequestHandler::handleException($this->middleWareManager, $e);
            Task::execute($coroutine, $this->context);
        }

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));

    }

    public function doRun()
    {
        /** @var Response $response */
        $response = $this->context->get("swoole_response");
        $msg = (yield $this->middleWareManager->executeFilters());
        if (null !== $msg) {
            $response->success($msg);
            return;
        }

        $dispatcher = Di::make(Dispatcher::class);
        yield $dispatcher->dispatch($this->request, $this->context);
        yield $this->middleWareManager->executePostFilters($response);
    }
}
