<?php

namespace Zan\Framework\Utilities\Types;


class Enum
{

    protected static $enum = null;

    public static function toArray()
    {
        return static::getConstants();
    }

    public static function getConstants()
    {
        if (static::$enum) {
            return static::$enum;
        }
        $oClass = new \ReflectionClass(static::class);
        static::$enum = $oClass->getConstants();
        return static::$enum;
    }
}