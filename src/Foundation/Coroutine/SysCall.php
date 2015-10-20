<?php
namespace Zan\Framework\Foundation\Coroutine;

class SysCall
{

    public static function end($words)
    {
        return new RetVal($words);
    }
}