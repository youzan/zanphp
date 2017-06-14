<?php

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Foundation\ExceptionHandler;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Contract\Network\RequestPostFilter;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Network\Http\RequestExceptionHandlerChain;
use Zan\Framework\Utilities\DesignPattern\Context;


class MiddlewareManager
{
    private $middlewareConfig;
    private $request;
    private $context;
    private $middleware = [];

    public function __construct(Request $request, Context $context)
    {
        $this->middlewareConfig = MiddlewareConfig::getInstance();
        $this->request = $request;
        $this->context = $context;

        $this->initMiddleware();
    }

    public function executeFilters()
    {
        $middleware = $this->middleware;
        foreach ($middleware as $filter) {
            if (!$filter instanceof RequestFilter) {
                continue;
            }

            $response = (yield $filter->doFilter($this->request, $this->context));
            if (null !== $response) {
                yield $response;
                return;
            }
        }
    }

    public function handleHttpException(\Exception $e)
    {
        $handlerChain = array_filter($this->middleware, function($v) {
            return $v instanceof ExceptionHandler;
        });
        yield RequestExceptionHandlerChain::getInstance()->handle($e, $handlerChain);
    }

    public function handleException(\Exception $e)
    {
        $middleware = $this->middleware;

        foreach ($middleware as $filter) {
            if (!$filter instanceof ExceptionHandler) {
                continue;
            }

            try {
                $e = (yield $filter->handle($e));
            } catch (\Throwable $t) {
                yield t2ex($t);
                return;
            } catch (\Exception $handlerException) {
                yield $handlerException;
                return;
            }
        }
        yield $e;
    }

    public function executePostFilters($response)
    {
        $middleware = $this->middleware;
        foreach ($middleware as $filter) {
            if (!$filter instanceof RequestPostFilter) {
                continue;
            }
            yield $filter->postFilter($this->request, $response, $this->context);
        }
    }

    public function executeTerminators($response)
    {
        $middleware = $this->middleware;
        foreach ($middleware as $filter) {
            if (!$filter instanceof RequestTerminator) {
                continue;
            }
            yield $filter->terminate($this->request, $response, $this->context);
        }
    }

    private function initMiddleware()
    {
        $middleware = [];
        $groupValues = $this->middlewareConfig->getRequestFilters($this->request);
        $groupValues = $this->middlewareConfig->addExceptionHandlers($this->request, $groupValues);
        $groupValues = $this->middlewareConfig->addBaseFilters($groupValues);
        $groupValues = $this->middlewareConfig->addBaseTerminators($groupValues);
        foreach ($groupValues as $groupValue) {
            $objectName = $this->getObject($groupValue);
            $obj = new $objectName();
            $middleware[$objectName] = $obj;
        }
        $this->middleware = $middleware;
    }

    private function getObject($objectName)
    {
        return $objectName;
    }
}
