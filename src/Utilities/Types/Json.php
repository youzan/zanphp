<?php

namespace Zan\Framework\Utilities\Types;

class Json
{
    public static function encode($data, $options = null, $depth = 512)
    {
        if (is_null($options)) {
            $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        }
        return json_encode($data, $options, $depth);
    }

    public static function decode($data, $assoc = true, $depth = 512, $options = JSON_BIGINT_AS_STRING)
    {
        if (!is_string($data)) {
            return $data;
        }
        return json_decode($data, $assoc, $depth, $options);
    }
}