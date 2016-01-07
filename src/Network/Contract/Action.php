<?php
namespace Zan\Framework\Network\Contract;

class Action {

    public $id;
    /**
     * @var Controller
     */
    public $controller;

    public function __construct($id, $controller)
    {
        $this->id = $id;
        $this->controller = $controller;
    }
}
