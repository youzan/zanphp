<?php

namespace Kdt\Iron\Nova\Service;


use Kdt\Iron\Nova\Foundation\Traits\StructSpecManager;
use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TType;



class StructValidator
{
    const INPUT = 1;
    const OUTPUT = 2;

    private static $outputIgnoreValidVar = ["success", "novaNull", "novaEmptyList"];

    /**
     * client: 验证返回值required字段
     * server: 验证接收参数required字段
     * @param array $args
     * @param array $inputStruct
     * @param $side
     */
    public static function validateInput(array $args, array $inputStruct, $side)
    {
        foreach ($inputStruct as $pos => $spec) {
            $path = "[{$spec['var']}]";

            if (isset($args[$spec["var"]])) {
                static::validateHelper($args[$spec["var"]], $spec, $path, $side, static::INPUT);
            } /*else if (isset($args[$pos-1])) {
                static::validateHelper($args[$pos-1], $spec, $path);
            } */else {
                if ($side === Packer::SERVER) {
                    // server input -> client 请求参数 不允许为 null
                    if (isset($subSpec["required"])) {
                        static::validateFail("$path is required", $side, static::INPUT);
                    }
                } else if ($side === Packer::CLIENT) {
                    // client input -> server 返回值 允许为 null
                    continue;
                }
            }
        }
    }

    /**
     * client: 验证请求参数required字段
     * server: 验证返回值required字段
     * @param array $outputStruct
     * @param $side
     */
    public static function validateOutput(array $outputStruct, $side)
    {
        foreach ($outputStruct as $pos => $spec) {
            if ($spec["value"] !== null) {
                $path = $spec["var"] === "success" ? "\$return" : $spec["var"];
                static::validateHelper($spec["value"], $spec, $path, $side, static::OUTPUT);
            } else {
                if ($side === Packer::SERVER) {
                    // server output -> 返回值 允许为 null
                    continue;
                } else if ($side === Packer::CLIENT) {
                    if (in_array($spec["var"], static::$outputIgnoreValidVar, true)) {
                        continue;
                    }
                    // client output -> 请求参数 不允许为 null
                    if (isset($spec["var"])) {
                        static::validateFail("var {$spec["var"]} is required", $side, static::OUTPUT);
                    } else {
                        static::validateFail("pos $pos is required", $side, static::OUTPUT);
                    }
                }
            }
        }
    }

    /**
     * @param array $argVal
     * @param array $spec
     * @param string $path
     * @param $side
     * @param $type
     */
    private static function validateHelper($argVal, $spec, $path, $side, $type)
    {
        switch ($spec["type"]) {

            case TType::STRUCT:
                if (!method_exists($argVal, "getStructSpec")) {
                    continue; // or throw ?!
                }

                /* @var StructSpecManager $argVal */
                foreach ($argVal->getStructSpec() as $subPos => $subSpec) {
                    if (isset($argVal->{$subSpec["var"]})) {
                        static::validateHelper($argVal->{$subSpec["var"]}, $subSpec, "$path.{$subSpec["var"]}", $side, $type);
                        continue;
                    } else {
                        if (isset($subSpec["required"])) {
                            static::validateFail("$path.{$subSpec["var"]} is required", $side, $type);
                        }
                    }
                }
                break;

            case TType::MAP:
                foreach ($argVal as $key => $arg) {
                    static::validateHelper($key, $spec["key"], "{$path}.<$key>", $side, $type);
                    static::validateHelper($arg, $spec["val"], "{$path}.$key", $side, $type);
                }
                break;

            case TType::SET:
                foreach ($argVal as $i => $arg) {
                    static::validateHelper($arg, $spec["elem"], "{$path}[$i]", $side, $type);
                }
                break;

            case TType::LST:
                foreach ($argVal as $i => $arg) {
                    static::validateHelper($arg, $spec["elem"], "{$path}[$i]", $side, $type);
                }
                break;

            default:
                if (isset($spec["required"]) && $argVal === null) {
                    static::validateFail("$path is required", $side, $type);
                }
        }
    }

    private static function validateFail($desc, $side, $type, $code = 500)
    {
        switch (true) {
            case $side === Packer::CLIENT && $type === static::INPUT:
                $msg = "Nova response value validate fail in client-side ";
                break;
            case $side === Packer::CLIENT && $type === static::OUTPUT:
                $msg = "Nova request arguments validate fail in client-side";
                break;
            case $side === Packer::SERVER && $type === static::INPUT:
                $msg = "Nova request arguments validate fail in server-side";
                break;
            case $side === Packer::SERVER && $type === static::OUTPUT:
                $msg = "Nova response value validate fail in server-side";
                break;
            default:
                $msg = "Nova value validate fail";
        }

        throw new TApplicationException("$msg: $desc", $code);
    }
}