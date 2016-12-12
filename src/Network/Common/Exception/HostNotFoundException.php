<?php
namespace Zan\Framework\Network\Common\Exception;

use Exception;
use Zan\Framework\Foundation\Exception\SystemException;

class HostNotFoundException extends SystemException
{
    public function __construct($message = '', $code = 408, Exception $previous = null, array $metaData = [])
    {
        parent::__construct($message, $code, $previous);
    }
}