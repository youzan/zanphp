<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Exception\BadRequestHttp;
use Zan\Framework\Network\Exception\InvalidRoute;

class Controller extends \Zan\Framework\Foundation\Domain\Controller{

    public $defaultAction = 'index';
    public $actionParams = [];

    public function runAction($actionName, $params = [])
    {
        $action = $this->createAction($actionName);
        if ($action === null) {
            throw new InvalidRoute('Unable to resolve the request: '.$actionName);
        }
        $result = $action->runWithParams($params);

        if ($result instanceof Response) {
            return $result;
        }
        $response = new Response();
        if ($result !== null) {
            $response->setData($result);
        }
        return $response;
    }

    public function createAction($actionName)
    {
        $actionName = !$actionName ? $this->defaultAction : $actionName;

        if (!preg_match('/^[a-z][a-z0-9]+$/', $actionName)) {
            return null;
        }
        $methodName = str_replace(' ', '', ucwords($actionName));
        if (!method_exists($this, $methodName)) {
            return null;
        }
        $method = new \ReflectionMethod($this, $methodName);
        if ($method->isPublic() && $method->getName() === $methodName)
            return new Action($actionName, $this, $methodName);

        return null;
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