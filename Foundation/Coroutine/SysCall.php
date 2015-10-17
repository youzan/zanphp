<?php
namespace Zanphp\Zan\Foundation\Coroutine;

class SysCall
{

    public static function end($words)
    {
        return new RetVal($words);
    }
}