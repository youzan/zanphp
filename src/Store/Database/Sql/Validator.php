<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/3/1
 * Time: 下午6:03
 */
namespace Zan\Framework\Store\Database\Sql;

class Validator
{
    public static function realEscape($value, $callback = null)
    {
        if (null != $callback && is_object($callback)) {
            return call_user_func($callback, $value);
        }
        return addslashes($value);
    }
}