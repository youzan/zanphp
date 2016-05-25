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

use RuntimeException;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class Dispatcher
{
    public function dispatch(Request $request, Context $context)
    {
        $controllerName = $context->get('controller_name');
        $action = $context->get('action_name');

        $controller = $this->getControllerClass($controllerName);
        if(!class_exists($controller)) {
            throw new RuntimeException("controller:{$controller} not found");
        }

        $controller = new $controller($request, $context);
        if(!is_callable([$controller, $action])) {
            throw new RuntimeException("action:{$action} is not callable in controller:" . get_class($controller));
        }

        if(method_exists($controller,'init')){
            yield $controller->init();
        }
        yield $controller->$action();
    }

    private function getControllerClass($controllerName)
    {
        $parts = array_filter(explode('/', $controllerName));
        $controllerName = join('\\', array_map('ucfirst', $parts));
        $app = Application::getInstance();
        return $app->getNamespace() . 'Controller\\' .  $controllerName . 'Controller';
    }
}