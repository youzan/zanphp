<?php

namespace Zan\Framework\Utilities\Types;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Number
{
    public static function floatToString($float) /* string */
    {
        if(is_string($float)) {
            return $float;
        }

        if(!is_float($float)) {
            throw new InvalidArgumentException('invalid argument for Number::floatToString(' . $float . ')');
        }

        $string = (string) $float;
        $string = str_replace('.', '', $string);
        $string = ltrim($string, '0');

        return $string;
    }
}