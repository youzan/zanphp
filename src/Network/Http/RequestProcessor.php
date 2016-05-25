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

use Generator;
use Zan\Framework\Foundation\Coroutine\Context;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Domain\Controller;
use Zan\Framework\Network\Http\Exception\InvalidRoute;
use Zan\Framework\Network\Http\Filter\FilterChain;

class RequestProcessor {

    private $context;
    private $request;
    private $response;
    private $filterChain;
    private $appNamespace = 'Zanhttp';

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->context  = new context();
        $this->filterChain = FilterChain::instance();
    }

    public function run($route)
    {
        $controller = $this->createController($route);

        if (!($controller instanceof Controller)) {
            throw new InvalidRoute('Not found controller:'.$controller);
        }
        $action = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new InvalidRoute('Class does not exist method '. get_class($controller).'::'.$action);
        }
        $this->doPreFilter();
        $result = $controller->$action();
        if ($result instanceof Generator) {
            $task = new Task($result);
            $task->run();
        }
        $this->doPostFilter();
    }

    private function doPreFilter()
    {
        $this->filterChain->doFilter($this->request, $this->response, $this->context);
    }

    private function doPostFilter()
    {
        $this->filterChain->setStepToPost();
        $this->filterChain->doFilter($this->request, $this->response, $this->context);
    }

    private function createController($route)
    {
        $module    = ucwords($route['module']);
        $className = ucwords($route['controller']);

        if (!preg_match('%^[A-Z][a-zA-Z][a-z0-9]*$%', $className)) {
            return null;
        }
        $className  = str_replace(' ', '', $className);
        $controller = ltrim($this->appNamespace . '\\' . $module . '\\Controller\\'. $className);

        if (!class_exists($controller)) {
            return null;
        }
        return new $controller($this->request, $this->response);
    }

}