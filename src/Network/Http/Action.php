<?php

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