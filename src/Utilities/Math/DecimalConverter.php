<?php

namespace Zan\Framework\Utilities\Math;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class DecimalConverter
{
    private static $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/';

    //从10机制转换成其他进制
    public static function decTo($num,$to)
    {
        if ( ! is_numeric($num) || ! is_numeric($to) || $to > 64 || $to < 0) {
            throw new InvalidArgumentException('参数不合法');
        }

        $result = '';

        do {
            $result = self::$dict[bcmod($num, $to)] . $result;
            $num = bcdiv($num, $to);
        } while ($num > 0);

        return $result;
    }

    //从其他进制转化成10进制
    public static function toDec($num,$from)
    {
        if (! is_numeric($from) || $from > 64 || $from < 0) {
            throw new InvalidArgumentException('参数不合法');
        }

        $result = 0;
        $length = strlen($num);
        for($i = 0; $i < $length; $i++) {
            $pos = strpos(self::$dict,$num[$i]);
            if ($pos === false) {
                throw new InvalidArgumentException('含有非法字符');
            }

            $result = bcadd($result, bcmul($pos, bcpow($from, $length-1-$i)));
        }
        return $result;
    }

    //进制转换
    public static function convert($num,$from,$to)
    {
        if (! is_numeric($from) || $from > 64 || $from < 0 || ! is_numeric($to) || $to > 64 || $to < 0 ) {
            throw new InvalidArgumentException('参数不合法');
        }

        if($from == 10) {
            return self::decTo($num,$to);
        }

        if($to == 10) {
            return self::toDec($num,$from);
        }

        $dec = self::toDec($num,$from);
        return self::decTo($dec,$to);
    }
}