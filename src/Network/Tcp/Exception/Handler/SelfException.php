<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/8
 * Time: 下午6:24
 */

namespace Zan\Framework\Network\Tcp\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;

class SelfException implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        sys_error("SelfException handle: ".$e->getMessage());
        throw new \Exception("SelfException", 0);
    }
}
