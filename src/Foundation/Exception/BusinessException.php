<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/9
 * Time: 10:34
 */

namespace Zan\Framework\Foundation\Exception;


class BusinessException extends ZanException
{
    /**
     * 验证是否为业务异常编码
     * @param int $code
     * @return bool
     */
    public static function isValidCode($code)
    {
        return ($code >= 10000 && $code <= 60000) || strlen($code) === 9;
    }
}