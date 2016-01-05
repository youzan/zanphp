<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Contract\Controller;

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
        $this->controller->beforeAction();
        $result = call_user_func_array([$this->controller, $this->actionMethod], $args);
        $result = $this->controller->afterAction($result);

        return $result;
    }


}