<?php

namespace Zan\Framework\Contract\Network;

use Zan\Framework\Utilities\DesignPattern\Context;

interface RequestFilter
{
    /**
     * @param Request $request
     * @param Context $context
     * @return \Zan\Framework\Contract\Network\Response
     */
    public function doFilter(Request $request, Context $context);
}