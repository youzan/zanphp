<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Network\Exception\InvalidRoute;
use Zan\Framework\Network\Http\Action;

class Controller {

    public $layout;
    public $action;
    public $defaultAction = 'index';

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

}
