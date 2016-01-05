<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Network\Exception\InvalidRoute;
use Zan\Framework\Network\Http\Action;

class Controller {

    public $layout;
    public $action;
    public $defaultAction = 'index';

    public function runAction($id, $params = [])
    {
        $action = $this->createAction($id);
        if ($action === null) {
            throw new InvalidRoute('Unable to resolve the request: '.$id);
        }
        return $action->runWithParams($params);
    }

    public function createAction($id)
    {
        $id = !$id ? $this->defaultAction : $id;

        if (!preg_match('/^[a-z][a-z0-9]+$/', $id)) {
            return null;
        }
        $methodName = 'action' . str_replace(' ', '', ucwords($id));

        if (!method_exists($this, $methodName)) {
            return null;
        }
        $method = new \ReflectionMethod($this, $methodName);
        if ($method->isPublic() && $method->getName() === $methodName)
            return new Action($id, $this, $methodName);

        return null;
    }

    public function bindActionParams($action, $params)
    {
        return [];
    }

    public function beforeAction()
    {
        return true;
    }

    public function afterAction()
    {
        return true;
    }
}
