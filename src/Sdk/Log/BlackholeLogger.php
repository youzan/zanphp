<?php

namespace Zan\Framework\Sdk\Log;


class BlackholeLogger extends BaseLogger
{

    public function init()
    {
    }

    public function format($level, $message, $context)
    {
        return null;
    }

    public function write($level, $message, array $context = array())
    {
        yield null;
    }

}
