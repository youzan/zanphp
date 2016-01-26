<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Http\Request;
use Zan\Framework\Network\Http\Response;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class Controller {

    protected $context;
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response, Context $context)
    {
        $this->context = $context;
        $this->request = $request;
        $this->respones = $response;
    }

    public function display()
    {

    }

    public function assign()
    {

    }

    public function r($code, $msg, $data)
    {
    }

    public function output($data)
    {
        $this->respones->setData($data);
        $this->respones->send();
    }

}
