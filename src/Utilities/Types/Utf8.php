<?php
/**
 * Created by PhpStorm.
 * User: suqian
 * Date: 16/3/22
 * Time: 下午2:51
 */

namespace Zan\Framework\Utilities\Types;


class Utf8
{

    public static function strSplit($str, $split_length = 1)
    {
        $split_length = (int) $split_length;

        if (Text::isAscii($str))
        {
            return str_split($str, $split_length);
        }

        if ($split_length < 1)
        {
            return FALSE;
        }

        if (mb_strlen($str) <= $split_length)
        {
            return array($str);
        }

        preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);

        return $matches[0];
    }
}