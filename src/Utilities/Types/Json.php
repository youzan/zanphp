<?php

namespace Zan\Framework\Utilities\Types;

class Json
{
    /**
     * @param mixed $value
     * @param null $options
     * @param int $depth
     * @return string
     */
    public static function encode($value, $options = null, $depth = 512)
    {
        if (is_null($options)) {
            $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        }
        return json_encode($value, $options, $depth);
    }

    /**
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public static function decode($json, $assoc = true, $depth = 512, $options = JSON_BIGINT_AS_STRING)
    {
        if (!is_string($json)) {
            return $json;
        }
        return json_decode($json, $assoc, $depth, $options);
    }
}