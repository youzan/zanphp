<?php

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Nova\Framework\Generic\Service\GenericRequest;
use Kdt\Iron\Nova\Foundation\Protocol\TStruct;
use Kdt\Iron\Nova\Foundation\TSpecification;
use Kdt\Iron\Nova\Nova;
use Kdt\Iron\Nova\Service\ClassMap;
use Thrift\Type\TType;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Network\Exception\GenericInvokeException;

final class GenericRequestCodec
{
    const GENERIC_SERVICE_PREFIX = 'com.youzan.nova.framework.generic.service';

    /**
     * @var array 与卡门约定透传的内部参数
     */
    public static $carmenInternalArgs = ["async", "request_ip", "kdt_id", "admin_id", "client_id"];

    public static function isGenericService($serviceName)
    {
        return 0 === strncasecmp(static::GENERIC_SERVICE_PREFIX, $serviceName, strlen(static::GENERIC_SERVICE_PREFIX));
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param mixed $result
     * @return string
     */
    public static function encode($serviceName, $methodName, $result)
    {
        if ($result instanceof \Throwable || $result instanceof \Exception) {

            return json_encode([
                "error_response" => [
                    "code"      => $result->getCode() ?:0 ,
                    "message"   => $result->getMessage(),
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } else {

            /* @var $classSpec TSpecification */
            /* @var $classMap ClassMap */

            /*$classMap = ClassMap::getInstance();
            $classSpec = $classMap->getSpec($serviceName);
            $resultSpec = $classSpec->getOutputStructSpec($methodName);

            static::cleanSpec($resultSpec, $result);*/

            return json_encode([
                "response" => $result
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
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
        if ($args[0] && is_object($args[0]) && ($args[0] instanceof GenericRequest)) {
            static::checkAndParse($args[0]);
            return $args[0];
        } else {
            throw new GenericInvokeException("Invalid GenericRequest");
        }
    }

    private static function checkAndParse(GenericRequest $request)
    {
        /* @var $classSpec TSpecification */
        /* @var $classMap ClassMap */

        $serviceName = $request->serviceName;
        $methodName = $request->methodName;
        $params = $request->methodParams;
        // $paramTypes = $request->parameterTypes; // list params

        if (!$serviceName || !$methodName) {
            throw new GenericInvokeException("Invalid generic request service or method");
        }

        $serviceName = str_replace('.', '\\', ucwords($serviceName, '.'));
        $request->serviceName = $serviceName;

        $classMap = ClassMap::getInstance();
        $classSpec = $classMap->getSpec($serviceName);
        if ($classSpec === null) {
            throw new GenericInvokeException("Service \"$serviceName\" not found");
        }

        $paramSpec = $classSpec->getInputStructSpec($methodName);
        if ($paramSpec === null) {
            throw new GenericInvokeException("Service Method \"$methodName\" not found");
        }

        $expectedParamNum = count($paramSpec);
        if ($expectedParamNum > 0) {
            $params = static::parseMapParams($params); // map params
            // $params = static::parseListParams($params, $paramTypes); // list params

            $realParamNum = count($params);
            $requiredParamsNum = static::getRequiredParamsNum($serviceName, $methodName);
            // assert($requiredParamsNum <= $expectedParamNum);
            if ($realParamNum < $requiredParamsNum) {
                throw new GenericInvokeException("Too few arguments to $methodName, $realParamNum passed and at least $requiredParamsNum expected");
            }

            $request->methodParams = static::getParamsBySpec($paramSpec, $params, $request);
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
    /*
    private static function parseListParams($rawArgs, $paramTypes)
    {
        if (!is_array($rawArgs) || !is_array($paramTypes) || count($rawArgs) !== count($paramTypes)) {
            throw new GenericInvokeException("Invalid generic request parameters");
        }

        $args = [];
        foreach ($rawArgs as $rawArg) {
            // fix 根据卡门传递的paramType判断哪些参数需要json_decode
            $args[] = static::decodeJson($rawArg);
        }
        return $args;
    }
    //*/

    /**
     * 处理 map<string, json> 类型参数
     * @param string $rawArgs
     * @return array
     * @throws GenericInvokeException
     */
    private static function parseMapParams($rawArgs)
    {
        $args = static::decodeJson($rawArgs, "Invalid generic request arguments");
        if (!is_array($args)) {
            throw new GenericInvokeException("Invalid generic request arguments");
        }
        return $args;
    }

    private static function getParamsBySpec(array $specs, array $rawArgs, GenericRequest $request)
    {
        $arguments = [];
        $defaultValues = static::getParamsDefaultValues($request->serviceName, $request->methodName);
        foreach ($specs as $pos => $item) {
            // $arguments[] = static::parseSpec($item, $rawArgs[$pos - 1], $pos); // list params

            // map params
            $paramName = $item["var"];
            if (isset($rawArgs[$paramName])) {
                $arguments[$paramName] = static::parseSpec($item, $rawArgs[$paramName], $paramName);
            } else if (isset($defaultValues[$pos])) {
                $arguments[$paramName] = $defaultValues[$pos];
            } else {
                throw new GenericInvokeException("Missing argument <$paramName>");
            }
        }
        return $arguments;
    }

    /**
     * 获取服务方法实现的最小参数值
     * @param string $serviceName
     * @param string $methodName
     * @return int
     */
    private static function getRequiredParamsNum($serviceName, $methodName)
    {
        $method = static::getServiceImplReflectMethod($serviceName, $methodName);
        return $method->getNumberOfRequiredParameters();
    }

    /**
     * 获取服务方法参数的默认值
     * @param string $serviceName
     * @param string $methodName
     * @return array [pos => count]
     */
    private static function getParamsDefaultValues($serviceName, $methodName)
    {
        static $cache = [];
        $key = "$serviceName::$methodName";

        if (!isset($cache[$key])) {
            $args = [];
            $reflectParams = static::getServiceImplReflectMethod($serviceName, $methodName)->getParameters();
            foreach ($reflectParams as $reflectParam) {
                // $reflectParam->getName(); // name与thrift中var可能不一致,需要根据pos处理
                $pos = $reflectParam->getPosition();
                if ($reflectParam->isOptional()) {
                    // thrift 协议的参数位置从1开始
                    $args[$pos + 1] = $reflectParam->getDefaultValue();
                }
            }
            $cache[$key] = $args;
        }

        return $cache[$key];
    }

    /**
     * 获取服务实现方法的\ReflectionMethod
     * @param $serviceName
     * @param $methodName
     * @return \ReflectionMethod
     * @throws GenericInvokeException
     */
    private static function getServiceImplReflectMethod($serviceName, $methodName)
    {
        static $cache = [];
        $key = "$serviceName::$methodName";

        if (!isset($cache[$key])) {
            $appImplClassName = static::fromNovaServiceToServiceImpl($serviceName);
            $class = new \ReflectionClass($appImplClassName);
            if (!$class->hasMethod($methodName)) {
                throw new GenericInvokeException("Service implement method \"$appImplClassName::$methodName\" not found");
            }

            $method = $class->getMethod($methodName);
            if (!$method->isPublic() || $method->isAbstract()) {
                throw new GenericInvokeException("\"$appImplClassName::$methodName\" can not be accessed");
            }

            $cache[$key] = $method;
        }

        return $cache[$key];
    }

    /**
     * 从novaService名称映射到服务实现class名称
     * @param string $serviceName
     * @return string
     * @throws GenericInvokeException
     */
    private static function fromNovaServiceToServiceImpl($serviceName)
    {
        $app = Application::getInstance();
        $appNamespace = $app->getNamespace();
        $appName = $app->getName();
        $appImplClassName = $appNamespace . Nova::removeNovaNamespace($serviceName, $appName);
        if (!class_exists($appImplClassName)) {
            throw new GenericInvokeException("Service implement class \"$appImplClassName\" not found");
        }
        return $appImplClassName;
    }

    /**
     * 根据spec递归解析变量
     * @param array $specItem
     * @param mixed $rawValue
     * @param string $paramName
     * @param int $deep
     * @return mixed
     * @throws GenericInvokeException
     */
    private static function parseSpec($specItem, $rawValue, $paramName, $deep = 1)
    {
        $expectedTType = $specItem["type"];

        switch ($expectedTType) {

            case TType::BOOL:
                $boolVal = filter_var($rawValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolVal === null) {
                    throw new GenericInvokeException("Invalid <$paramName> type, expects bool");
                }
                return $boolVal;

            case TType::BYTE:
            case TType::I08:
            case TType::I16:
            case TType::I32:
            case TType::I64:
                $intVal = filter_var($rawValue, FILTER_VALIDATE_INT);
                if ($intVal === false) {
                    throw new GenericInvokeException("Invalid <$paramName> type, expects int");
                }
                return $intVal;

            case TType::DOUBLE:
                $floatVal = filter_var($rawValue, FILTER_VALIDATE_FLOAT);
                if ($floatVal === false) {
                    throw new GenericInvokeException("Invalid <$paramName> type, expects float");
                }
                return $floatVal;

            case TType::STRING:
                if (!is_string($rawValue)) {
                    throw new GenericInvokeException("Invalid <$paramName> type, expects string");
                }

                return $rawValue;

            case TType::STRUCT:
                $rawValue = static::parseComplexArgument($deep, $rawValue, $paramName, "object");

                /* @var $structObject TStruct */
                $structObject = new $specItem["class"];
                $structSpec = $structObject->getStructSpec();

                foreach ($structSpec as $pos => $item) {
                    $propName = $item["var"];
                    if (isset($rawValue[$propName])) {
                        $structObject->$propName = static::parseSpec($item, $rawValue[$propName], "$paramName.$propName", $deep + 1);
                    } else {
                        $structObject->$propName = null;
                    }
                }
                return $structObject;

            case TType::MAP:
                $rawValue = static::parseComplexArgument($deep, $rawValue, $paramName, "map");

                $map = [];
                foreach ($rawValue as $key => $value) {
                    $key = static::parseSpec($specItem["key"], $key, "$paramName.$key", $deep + 1);
                    $map[$key] = static::parseSpec($specItem["val"], $value, "$paramName.$key", $deep + 1);
                }
                return $map;

            case TType::SET:
                $rawValue = static::parseComplexArgument($deep, $rawValue, $paramName, "set");

                $set = [];
                foreach ($rawValue as $i => $value) {
                    $set[] = static::parseSpec($specItem["elem"], $value, "{$paramName}[$i]", $deep + 1);
                }
                return /*array_unique(*/$set/*)*/;

            case TType::LST:
                $rawValue = static::parseComplexArgument($deep, $rawValue, $paramName, "list");

                $list = [];
                foreach ($rawValue as $i => $value) {
                    $list[] = static::parseSpec($specItem["elem"], $value, "{$paramName}[$i]", $deep + 1);
                }
                return $list;

            case TType::UTF7:
            case TType::UTF8:
            case TType::UTF16:
            case TType::VOID:
            case TType::STOP:
            default:
                throw new GenericInvokeException("Unsupported argument type \"$expectedTType\"");
        }
    }

    private static function parseComplexArgument($deep, $rawValue, $paramName, $expect = "")
    {
        if ($deep === 1) {
            if (!is_string($rawValue)) {
                throw new GenericInvokeException("Invalid <$paramName> type, expects $expect");
            }
            $rawValue = static::decodeJson($rawValue, "Invalid <$paramName> type, expects $expect");
        }

        if (!is_array($rawValue)) {
            throw new GenericInvokeException("Invalid <$paramName> type, expects object");
        }

        return $rawValue;
    }

    private static function decodeJson($raw, $errMsg = null)
    {
        $data = json_decode($raw, true, 512, JSON_BIGINT_AS_STRING);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $last = json_last_error();
            if ($errMsg) {
                throw new GenericInvokeException($errMsg);
            } else {
                throw new GenericInvokeException("Unable to parse JSON data[$last]");
            }
        }
        return $data;
    }

    /**
     * 递归清除返回值中无用的 _TSPEC 属性
     * @param array $specItem
     * @param $result
     */
    private static function cleanSpec(array $specItem, &$result)
    {
        $expectedTType = $specItem["type"];

        switch ($expectedTType) {
            case TType::STRUCT:
                if (!($result instanceof TStruct)) {
                    break;
                }

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
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $key => &$value) {
                    static::cleanSpec($specItem["val"], $value);
                }
                unset($value);
                break;

            case TType::SET:
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $i => &$value) {
                    static::cleanSpec($specItem["elem"], $value);
                }
                unset($value);
                break;

            case TType::LST:
                if (!is_array($result)) {
                    break;
                }

                foreach ($result as $i => &$value) {
                    static::cleanSpec($specItem["elem"], $value);
                }
                unset($value);
                break;
        }
    }
}