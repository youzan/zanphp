<?php

namespace Kdt\Iron\Nova\Foundation\Protocol;

use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Exception as SysException;

abstract class TException extends SysException
{
    /**
     * Spec mgr
     */
    use StructSpecManager;

    /**
     * TException constructor.
     */
    public function __construct($message = "", $code = 0, SysException $previous = null)
    {
        $this->staticSpecInjecting();

        /**
         * 这个是为了兼容.thrift文件中定义的业务异常中的预定义的message信息
         * 对于这类Exception, 如果其message传递了默认值也就是"", 那么最终生成的实际对象的message
         * 会被下一行的父类构造方法中的message成员的赋值覆盖掉
         * 所以这里message入参为空的情况下, 先读取一下子类中是否存在已经定义的message, 如果存在值
         * 就用这个值传递给父类的构造方法
         */
        if (!$message and isset($this->message) and !empty($this->message)) {
            $message = $this->message;
        }
        parent::__construct($message, $code, $previous);
    }
}
