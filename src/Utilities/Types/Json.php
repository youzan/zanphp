<?php

namespace Zan\Framework\Utilities\Types;

class Json
{
    public static function encode($value, $options = null, $depth = 512)
    {
        if (is_null($options)) {
            $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        }
        return json_encode($value, $options, $depth);
    }

    public static function decode($json, $assoc = true, $depth = 512, $options = JSON_BIGINT_AS_STRING)
    {
        if (!is_string($json)) {
            return $json;
        }
        return json_decode($json, $assoc, $depth, $options);
    }
}