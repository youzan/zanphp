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

namespace Zan\Framework\Network\Server\Middleware;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Contract\Network\RequestFilter;
use Zan\Framework\Contract\Network\RequestTerminator;
use Zan\Framework\Utilities\DesignPattern\Context;


class MiddlewareManager
{
    private $middlewareConfig;
    private $request;
    private $context;
    private $middlewares = [];

    public function __construct(Request $request, Context $context)
    {
        $this->middlewareConfig = MiddlewareConfig::getInstance();
        $this->request = $request;
        $this->context = $context;

        $this->initMiddlewares();
    }

    public function executeFilters()
    {
        $middlewares = $this->middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof RequestFilter) {
                continue;
            }

            $response = (yield $middleware->doFilter($this->request, $this->context));
            if (null !== $response) {
                yield $response;
                return;
            }
        }
    }

    public function executeTerminators($response)
    {
        $middlewares = $this->middlewares;
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof RequestTerminator) {
                continue;
            }
            yield $middleware->terminate($this->request, $response, $this->context);
        }
    }

    private function initMiddlewares()
    {
        $middlewares = [];
        $groupValues = $this->middlewareConfig->getGroupValue($this->request);
        $groupValues = $this->middlewareConfig->addBaseFilters($groupValues);
        $groupValues = $this->middlewareConfig->addBaseTerminators($groupValues);
        foreach ($groupValues as $groupValue) {
            $objectName = $this->getObject($groupValue);
            $obj = new $objectName();
            $middlewares[$objectName] = $obj;
        }
        $this->middlewares = $middlewares;
    }

    private function getObject($objectName)
    {
        return $objectName;
    }
}
