<?php

namespace Zan\Framework\Utilities\Types;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Json
{
    /**
     * @param mixed $value
     * @param int $options
     * @param int $depth
     * @return string
     * @throws InvalidArgumentException
     */
    public static function encode($value, $options = 0, $depth = 512)
    {
        $args = func_get_args();
        $options = $options | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION;
        $data = json_encode($value, $options, $depth);

        if ($data === false) {
            $errno = json_last_error();

            if ($errno === JSON_ERROR_UTF8) {
                $data = json_encode(static::cleanUtf8($value), $options, $depth);
                if ($data === false) {
                    goto ex;
                }
            } else {
                ex:
                throw new InvalidArgumentException("JSON Encode Failed: " . json_last_error_msg(), $errno, null, $args);
            }
        }

        return $data;
    }

    /**
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function decode($json, $assoc = true, $depth = 512, $options = JSON_BIGINT_AS_STRING)
    {
        if (!is_string($json)) {
            return $json;
        }
        $args = func_get_args();
        $data = json_decode($json, $assoc, $depth, $options);

        $errno = json_last_error();
        if ($errno !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("JSON Decode Failed: " . json_last_error_msg() . ". [raw=$json]", $errno, null, $args);
        }

        return $data;
    }

    /**
     * 清除非utf8字符串, 处理Thrift异常信息json_encode编码失败
     * @param $value
     * @return array
     */
    private static function cleanUtf8($value)
    {
        $cleanScalar = function(&$string) {
            if (is_string($string)) {
                $isUtf8 = preg_match('//u', $string);
                if (!$isUtf8) {
                    $sanitize = function ($m) { return utf8_encode($m[0]); };
                    $string = preg_replace_callback('/[\x80-\xFF]+/', $sanitize, $string);
                }
            }
        };

        if (is_string($value)) {
            $cleanScalar($value);
        } else if (is_array($value)) {
            array_walk_recursive($value, $cleanScalar);
        }

        return $value;
    }
}