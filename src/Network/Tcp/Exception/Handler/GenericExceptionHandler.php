<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/3/7
 * Time: 下午7:46
 */
namespace Zan\Framework\Network\Tcp\Exception\Handler;

use Zan\Framework\Contract\Foundation\ExceptionHandler;

class GenericExceptionHandler implements ExceptionHandler
{
    public function handle(\Exception $e)
    {
        sys_error("GenericExceptionHandler handle: ".$e->getMessage());
        throw new \Exception("网络错误", 0);
    }
}
