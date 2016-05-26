<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/26
 * Time: 下午5:05
 */

namespace Zan\Framework\Sdk\Log;


class BlackholeLogger extends BaseLogger
{

    public function init()
    {
        $this->writer = null;
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