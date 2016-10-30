<?php

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Test\Service\GenericException;
use Com\Youzan\Test\Service\GenericRequest;
use Kdt\Iron\Nova\Foundation\Protocol\TStruct;
use Kdt\Iron\Nova\Foundation\TSpecification;
use Kdt\Iron\Nova\Nova;
use Kdt\Iron\Nova\Service\ClassMap;
use Thrift\Type\TType;

final class GenericRequestUtils
{
    const GENERIC_SERVICE_PREFIX = 'com.youzan.test.service';

    public static function isGenericService($serviceName)
    {
        return static::GENERIC_SERVICE_PREFIX
            === substr($serviceName, 0, strlen(static::GENERIC_SERVICE_PREFIX));
    }

    /**
     * @param string $novaServiceName
     * @param string  $methodName
     * @param $args
     * @return GenericRequest
     * @throws GenericException
     */
    public static function decode($novaServiceName, $methodName, $args)
    {
        $args = Nova::decodeServiceArgs($novaServiceName, $methodName, $args);
        if ($args[0] && ($args[0] instanceof GenericRequest)) {
            static::checkAndParse($args[0]);
            return $args[0];
        } else {
            throw new GenericException("Invalid GenericRequest");
        }
    }

    private static function checkAndParse(GenericRequest $request)
    {
        /* @var $classSpec TSpecification */
        /* @var $classMap ClassMap */

        $serviceName = $request->serviceName;
        $methodName = $request->methodName;
        $params = $request->methodParams;

        if (!$serviceName || !$methodName) {
            throw new GenericException("Invalid class or method");
        }

        $serviceName = str_replace('.', '\\', ucwords($serviceName, '.'));
        $request->serviceName = $serviceName;

        $classMap = ClassMap::getInstance();
        $classSpec = $classMap->getSpec($serviceName);
        if ($classSpec === null) {
            throw new GenericException("Missing Service \"$serviceName\"");
        }

        $paramSpec = $classSpec->getInputStructSpec($methodName);
        if ($paramSpec === null) {
            throw new GenericException("Missing Service Method \"$methodName\"");
        }

        $expectedParamNum = count($paramSpec);
        if ($expectedParamNum > 0) {
            $params = json_decode($params, true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($params)) {
                throw new GenericException("Invalid parameters codec");
            }
            $realParamNum = count($params);
            if ($realParamNum < $expectedParamNum) {
                throw new GenericException("Expects $expectedParamNum parameter, $realParamNum given");
            }
            $request->methodParams = static::parseSpecs($paramSpec, array_values($params));
        } else {
            $request->methodParams = [];
        }
    }

    private static function parseSpecs(array $specs, array $rawArgs)
    {
        $arguments = [];
        foreach ($specs as $pos => $item) {
            $arguments[] = static::parseSpec($item, $rawArgs[$pos - 1], $pos);
        }
        return $arguments;
    }

    private static function parseSpec($specItem, $rawValue, $pos = -1)
    {
        $expectedTType = $specItem["type"];

        switch ($expectedTType) {

            case TType::BOOL:
                return boolval($rawValue);

            case TType::I08:
            case TType::I16:
            case TType::I32:
            case TType::I64:
                return intval($rawValue);

            case TType::DOUBLE:
                return floatval($rawValue);
                break;


            case TType::BYTE:
            case TType::STRING:
                return strval($rawValue);
                break;

            case TType::STRUCT:
                if (!is_array($rawValue)) {
                    throw new GenericException("Invalid parameter type in position of $pos, expects struct");
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
                    throw new GenericException("Invalid parameter type in position of $pos, expects map");
                }

                $map = [];
                foreach ($rawValue as $key => $value) {
                    $key = static::parseSpec($specItem["key"], $key);
                    $map[$key] = static::parseSpec($specItem["val"], $value);
                }
                return $map;

            case TType::SET:
                if (!is_array($rawValue)) {
                    throw new GenericException("Invalid parameter type in position of $pos, expects set");
                }

                $set = [];
                foreach ($rawValue as $i => $value) {
                    $set[] = static::parseSpec($specItem["elem"], $value, $i + 1);
                }
                return /*array_unique(*/$set/*)*/;

            case TType::LST:
                if (!is_array($rawValue)) {
                    throw new GenericException("Invalid parameter type in position of $pos, expects list");
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
                return null;
        }
    }
}