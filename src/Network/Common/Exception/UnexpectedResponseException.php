<?php


namespace Zan\Framework\Network\Common\Exception;

use Zan\Framework\Foundation\Exception\SystemException;

class UnexpectedResponseException extends SystemException
{
    /**
     * 注意: 不要继续使用该属性获取信息了,改为调用 getMetadata() 方法
     * 该属性会被改成 protected
     * @var array
     */
    public $metaData;
}