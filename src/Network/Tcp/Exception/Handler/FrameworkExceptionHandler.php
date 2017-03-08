<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/8
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\Tcp\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;

class FrameworkExceptionHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        // Just return input exception
        sys_echo("FrameworkExceptionHandler handle: ".$e->getMessage());
        return $e;
    }
}
