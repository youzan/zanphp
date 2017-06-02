<?php
namespace Zan\Framework\Store\Database\Sql;

class Validator
{
    public static function realEscape($value, $callback = null)
    {
        if (null != $callback && is_object($callback)) {
            return call_user_func($callback, $value);
        }
        return is_int($value) ? $value : addslashes($value);
    }
}