<?php
/**
 * Created by PhpStorm.
 * User: marsnowxiao
 * Date: 2017/6/2
 * Time: 上午11:08
 */
namespace Zan\Framework\Foundation\Exception;

use Psr\Log\LogLevel;

class ConditionException extends ZanException
{
    public $logLevel = LogLevel::ERROR;
}