<?php

namespace Zan\Framework\Contract\Network;

use Zan\Framework\Utilities\DesignPattern\Context;

interface RequestPostFilter
{
    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return void
     */
    public function postFilter(Request $request, Response $response, Context $context);
}