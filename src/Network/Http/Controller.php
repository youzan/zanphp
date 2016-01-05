<?php

namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Network\Exception\BadRequestHttp;

class Controller extends  \Zan\Framework\Network\Contract\Controller{

    public $actionParams = [];

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

    public function beforeAction()
    {
        parent::beforeAction();
    }

    public function afterAction()
    {
        parent::afterAction();
    }

    public function redirect($url, $statusCode = 302)
    {
        return ;
    }
}