<?php

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Nova\Framework\Generic\Service\GenericRequest;
use Com\Youzan\Nova\Framework\Generic\Service\GenericResponse;
use Kdt\Iron\Nova\Foundation\Protocol\TStruct;
use Kdt\Iron\Nova\Foundation\TSpecification;
use Kdt\Iron\Nova\Nova;
use Kdt\Iron\Nova\Service\ClassMap;
use Thrift\Type\TType;
use Zan\Framework\Network\Exception\GenericInvokeException;

final class GenericRequestUtils
{
    const GENERIC_SERVICE_PREFIX = 'com.youzan.test.service';
    const RESPONSE_SUCCESS = 200;

    public static function isGenericService($serviceName)
    {
        return static::GENERIC_SERVICE_PREFIX === substr($serviceName, 0, strlen(static::GENERIC_SERVICE_PREFIX));
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param mixed $result
     * @param int $code
     * @param string $msg
     * @return GenericResponse
     */
    public static function encode($serviceName, $methodName, $result, $code = self::RESPONSE_SUCCESS, $msg = "")
    {
        /* @var $classSpec TSpecification */
        /* @var $classMap ClassMap */
        $classMap = ClassMap::getInstance();

        $classSpec = $classMap->getSpec($serviceName);
        $resultSpec = $classSpec->getOutputStructSpec($methodName);

        static::cleanSpec($resultSpec, $result);

        $response = new GenericResponse();
        $response->code = $code;
        $response->message = $msg;
        $response->data = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $response;
    }

    /**
     * @param string $novaServiceName
     * @param string $methodName
     * @param $args
     * @return GenericRequest
     * @throws GenericInvokeException
     */
    public static function decode($novaServiceName, $methodName, $args)
    {
        $args = Nova::decodeServiceArgs($novaServiceName, $methodName, $args);
        if ($args[0] && ($args[0] instanceof GenericRequest)) {
            static::checkAndParse($args[0]);
            return $args[0];
        } else {
            throw new GenericInvokeException("Invalid GenericRequest");
        }
    }

    public static function makeResponseByException(\Exception $ex) {
        $response = new GenericResponse();
        $code = $ex->getCode();
        $response->code = $code === static::RESPONSE_SUCCESS ? 0 : $code;
        $response->message = $ex->getMessage();
        $response->data = "";
        return $response;
    }

    private static function checkAndParse(GenericRequest $request)
    {
        /* @var $classSpec TSpecification */
        /* @var $classMap ClassMap */

        $serviceName = $request->serviceName;
        $methodName = $request->methodName;
        $params = $request->methodParams;
        // $paramTypes = $request->parameterTypes;

        if (!$serviceName || !$methodName) {
            throw new GenericInvokeException("Invalid generic request service or method");
        }

        $serviceName = str_replace('.', '\\', ucwords($serviceName, '.'));
        $request->serviceName = $serviceName;

        $classMap = ClassMap::getInstance();
        $classSpec = $classMap->getSpec($serviceName);
        if ($classSpec === null) {
            throw new GenericInvokeException("Missing Service \"$serviceName\"");
        }

        $paramSpec = $classSpec->getInputStructSpec($methodName);
        if ($paramSpec === null) {
            throw new GenericInvokeException("Missing Service Method \"$methodName\"");
        }

        // TODO bojack 反射获取必须参数个数~
        $expectedParamNum = count($paramSpec);
        if ($expectedParamNum > 0) {

            $params = static::parseMapParams($params); // map params
            // $params = static::parseListParams($params, $paramTypes); // list params

            $realParamNum = count($params);
            if ($realParamNum < $expectedParamNum) {
                throw new GenericInvokeException("Expects $expectedParamNum parameter, $realParamNum given");
            }

            $request->methodParams = static::getParamsBySpec($paramSpec, $params);
        } else {
            $request->methodParams = [];
        }
    }

    /**
     * 处理 list<string> 类型参数
     * @param $rawArgs
     * @param $paramTypes
     * @return array
     * @throws GenericInvokeException
     */
    private static function parseListParams($rawArgs, $paramTypes)
    {
        if (!is_array($rawArgs) || !is_array($paramTypes) || count($rawArgs) !== count($paramTypes)) {
            throw new GenericInvokeException("Invalid generic request parameters");
        }

        $args = [];
        foreach ($rawArgs as $rawArg) {
            // TODO 根据卡门传递的paramType判断哪些参数需要json_decode
            $args[] = json_decode($rawArg, true, 512, JSON_BIGINT_AS_STRING);
        }
        return $args;
    }

    /**
     * 处理 map<string, json> 类型参数
     * @param string $rawArgs
     * @return array
     * @throws GenericInvokeException
     */
    private static function parseMapParams($rawArgs)
    {
        if (is_array($rawArgs)) {
            return $rawArgs;
        }

        $args = json_decode($rawArgs, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($args)) {
            throw new GenericInvokeException("Invalid generic request parameters");
        }
        return $args;
    }

    private static function getParamsBySpec(array $specs, array $rawArgs)
    {
        $arguments = [];
        foreach ($specs as $pos => $item) {
            // $arguments[] = static::parseSpec($item, $rawArgs[$pos - 1], $pos); // list params

            // map params
            $paramName = $item["var"];
            if (!isset($rawArgs[$paramName])) {
                throw new GenericInvokeException("Missing method parameter \"$paramName\"");
            }
            $arguments[$paramName] = static::parseSpec($item, $rawArgs[$paramName], $pos);
        }
        return $arguments;
    }

    private static function parseSpec($specItem, $rawValue, $pos = -1)
    {
        $expectedTType = $specItem["type"];

        switch ($expectedTType) {

            case TType::BOOL:
                if (!is_scalar($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects bool");
                }
                return boolval($rawValue);

            case TType::I08:
            case TType::I16:
            case TType::I32:
            case TType::I64:
                if (!is_scalar($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects int");
                }
                return intval($rawValue);

            case TType::DOUBLE:
                if (!is_scalar($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects double");
                }
                return floatval($rawValue);
                break;


            case TType::BYTE:
            case TType::STRING:
                if (!is_scalar($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects byte|string");
                }
                return strval($rawValue);
                break;

            case TType::STRUCT:
                if (!is_array($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects struct");
                }

                /* @var $structObject TStruct */
                $structObject = new $specItem["class"];
                $structSpec = $structObject->getStructSpec();

                foreach ($structSpec as $pos => $item) {
                    $propName = $item["var"];
                    if (isset($rawValue[$propName])) {
                        $structObject->$propName = static::parseSpec($item, $rawValue[$propName], $pos);
                    } else {
                        $structObject->$propName = null;
                    }
                }
                return $structObject;

            case TType::MAP:
                if (!is_array($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects map");
                }

                $map = [];
                foreach ($rawValue as $key => $value) {
                    $key = static::parseSpec($specItem["key"], $key);
                    $map[$key] = static::parseSpec($specItem["val"], $value);
                }
                return $map;

            case TType::SET:
                if (!is_array($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects set");
                }

                $set = [];
                foreach ($rawValue as $i => $value) {
                    $set[] = static::parseSpec($specItem["elem"], $value, $i + 1);
                }
                return /*array_unique(*/$set/*)*/;

            case TType::LST:
                if (!is_array($rawValue)) {
                    throw new GenericInvokeException("Invalid parameter type in position of $pos, expects list");
                }

                $list = [];
                foreach ($rawValue as $i => $value) {
                    $list[] = static::parseSpec($specItem["elem"], $value, $i + 1);
                }
                return $list;

            case TType::UTF7:
            case TType::UTF8:
            case TType::UTF16:
            case TType::VOID:
            case TType::STOP:
            default:
                throw new GenericInvokeException("Unsupported type \"$expectedTType\"");
        }
    }

    private static function cleanSpec(array $specItem, &$result)
    {
        $expectedTType = $specItem["type"];

        switch ($expectedTType) {
            case TType::STRUCT:
                /* @var $result TStruct */
                $structSpec = $result->getStructSpec();
                foreach ($structSpec as $pos => $item) {
                    $propName = $item["var"];
                    if ($result->$propName !== null) {
                        static::cleanSpec($item, $result->$propName);
                    }
                }
                unset($result->_TSPEC);
                break;

            case TType::MAP:
                foreach ($result as $key => &$value) {
                    static::cleanSpec($specItem["val"], $value);
                }
                unset($value);
                break;

            case TType::SET:
                foreach ($result as $i => &$value) {
                    static::cleanSpec($specItem["elem"], $value);
                }
                unset($value);
                break;

            case TType::LST:
                foreach ($result as $i => &$value) {
                    static::cleanSpec($specItem["elem"], $value);
                }
                unset($value);
                break;
        }
    }
}