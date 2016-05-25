<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Http\Response\BaseResponse;
use Zan\Framework\Network\Http\Response\InternalErrorResponse;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\Http\Dispatcher;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Server\Monitor\Worker;

use swoole_http_response as SwooleHttpResponse;

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
        try{
            yield $this->doRun();
        } catch (\Exception $e) {
            $coroutine = RequestExceptionHandlerChain::getInstance()->handle($e);
            Task::execute($coroutine, $this->context);
            $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
        }
    }

    public function doRun()
    {
        $response = (yield $this->middleWareManager->executeFilters());
        if(null !== $response){
            $this->context->set('response', $response);
            yield $response->sendBy($this->swooleResponse);
            return;
        }

        $Dispatcher = Di::make(Dispatcher::class);
        $response = (yield $Dispatcher->dispatch($this->request, $this->context));
        if (null === $response) {
            $response = new InternalErrorResponse('网络错误', BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->context->set('response', $response);
        yield $response->sendBy($this->swooleResponse);

        $this->context->getEvent()->fire($this->context->get('request_end_event_name'));
    }
}
