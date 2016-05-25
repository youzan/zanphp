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

use Zan\Framework\Network\Http\Exception\BadRequestHttp;

class Action extends \Zan\Framework\Network\Contract\Action{

    public $actionMethod;

    public function __construct($id, Controller $controller, $actionMethod)
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller);
    }

    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        $result = call_user_func_array([$this->controller, $this->actionMethod], $args);

        return $result;
    }

    public function bindActionParams($action, $params)
    {
        $method = new \ReflectionMethod($this, $action->actionMethod);
        $args = $missing = $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $funcName = $param->getName();
            if (array_key_exists($funcName, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$funcName] = (array) $params[$funcName];
                } elseif (!is_array($params[$funcName])) {
                    $args[] = $actionParams[$funcName] = $params[$funcName];
                } else {
                    throw new BadRequestHttp('Invalid data received for parameter: '. implode(', ', $missing));
                }
                unset($params[$funcName]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$funcName] = $param->getDefaultValue();
            } else {
                $missing[] = $funcName;
            }
        }
        if (!empty($missing))
            throw new BadRequestHttp('Missing required parameters: '. implode(', ', $missing));

        $this->actionParams = $actionParams;

        return $args;
    }


}