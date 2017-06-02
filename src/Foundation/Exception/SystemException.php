<?php

namespace Zan\Framework\Foundation\Exception;

use Psr\Log\LogLevel;

class SystemException extends ZanException
{
    public $logLevel = LogLevel::ERROR;
}