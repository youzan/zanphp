<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Contract\Request;
use Zan\Framework\Network\Contract\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class Controller {

    protected $request;
    protected $response;
    protected $context;

    public function __construct(Request $request, Response $response, Context $context=null)
    {
        $this->request = $request;
        $this->respones = $response;
        $this->context = $context;
    }

    public function output($data)
    {
        $this->respones->setData($data);
        $this->respones->send();
    }

}
