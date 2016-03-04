<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/3/1
 * Time: 下午6:03
 */
namespace Zan\Framework\Store\Database\Mysql;

class Validator
{
    public static function validate($value)
    {
        return addslashes($value);
    }

    private function injectCheck($value)
    {
        return preg_grep('/select|insert|and|or|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i', $value);
    }
}