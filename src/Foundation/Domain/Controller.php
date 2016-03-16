<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class Controller {

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Context;
     */
    protected $context;

    public function __construct(Request $request, Context $context)
    {
        $this->request = $request;
        $this->context = $context;
    }

}
