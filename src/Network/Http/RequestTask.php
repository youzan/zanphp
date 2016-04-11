<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/14
 * Time: 00:02
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Server\Middleware\MiddlewareManager;
use Zan\Framework\Network\http\Dispatcher;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Foundation\Container\Di;

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

    public function __construct(Request $request, SwooleHttpResponse $swooleResponse, Context $context)
    {
        $this->request = $request;
        $this->swooleResponse = $swooleResponse;
        $this->context = $context;
    }

    public function run()
    {
//        $middlewareManager = MiddlewareManager::getInstance();
//
//        $response = (yield $middlewareManager->executeFilters($this->request, $this->context));
//        if(null !== $response){
//            yield $response->sendBy($this->swooleResponse);
//            return;
//        }

        $acl = new Acl($this->request);
        $result = (yield $acl->auth());
        if ($result !== null) {
            yield $result->sendBy($this->swooleResponse);
            return;
        }

        $Dispatcher = Di::make(Dispatcher::class);
        $response = (yield $Dispatcher->dispatch($this->request, $this->context));

        if (null === $response) {
            throw new ZanException('');
        } else {
            yield $response->sendBy($this->swooleResponse);
            return;
        }

        //yield $middlewareManager->executeTerminators($this->request, $response, $this->context);
    }


}
