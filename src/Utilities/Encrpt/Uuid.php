<?php

namespace Zan\Framework\Utilities\Encrpt;

class Uuid
{
    //临时用
    public static function get() {
        $name = uniqid("", true)
                . mt_rand(5,5900000000)
                . mt_rand(5,5900000000)
                . '_'
                . mt_rand()
                . '_'
                . mt_rand(5,5900000000)
                . '_'
                . time();

        $chars = md5($name);

        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);

        return $uuid;
    }
}