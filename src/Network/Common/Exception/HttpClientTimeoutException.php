<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/26
 * Time: 上午11:19
 */
namespace Zan\Framework\Network\Common\Exception;

use Exception;
use Zan\Framework\Foundation\Exception\SystemException;

class HttpClientTimeoutException extends SystemException
{
    public function __construct($message = '', $code = 408, Exception $previous = null, array $metaData = [])
    {
        parent::__construct($message, $code, $previous);
    }
}