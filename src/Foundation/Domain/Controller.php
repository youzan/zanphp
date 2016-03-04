<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Contract\Request;
use Zan\Framework\Network\Contract\Response;

class Controller {

    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
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
