<?php


namespace Zan\Framework\Network\Common\Exception;

use Zan\Framework\Foundation\Exception\SystemException;

class UnexpectedResponseException extends SystemException
{
    /**
     * @todo 去掉重载的定义
     * @var array
     */
    public $metaData;
}