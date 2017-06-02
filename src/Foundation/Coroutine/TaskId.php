<?php

namespace Zan\Framework\Foundation\Coroutine;


class TaskId
{
    private static $id = 0;

    public static function create()
    {
        if (self::$id >= PHP_INT_MAX) {
            self::$id = 1;
            return self::$id;
        }

        self::$id++;
        return self::$id;
    }
}