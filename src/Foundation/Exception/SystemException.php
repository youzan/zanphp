<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 10:33
 */

namespace Zan\Framework\Foundation\Exception;

use Psr\Log\LogLevel;

class SystemException extends ZanException
{
    public $logLevel = LogLevel::ERROR;
}