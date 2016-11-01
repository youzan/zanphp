<?php

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Nova\Framework\Generic\Service\GenericRequest;
use Com\Youzan\Nova\Framework\Generic\Service\GenericResponse;
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
    const RESPONSE_SUCCESS = 200;

    public static function isGenericService($serviceName)
    {
        return 0 === strncasecmp(static::GENERIC_SERVICE_PREFIX, $serviceName, strlen(static::GENERIC_SERVICE_PREFIX));
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param mixed $result
     * @return GenericResponse
     */
    public static function encode($serviceName, $methodName, $result)
    {
        /* @var $classSpec TSpecification */
        /* @var $classMap ClassMap */
        $classMap = ClassMap::getInstance();

        $classSpec = $classMap->getSpec($serviceName);
        $resultSpec = $classSpec->getOutputStructSpec($methodName);

        static::cleanSpec($resultSpec, $result);

        $response = new GenericResponse();
        $response->code = self::RESPONSE_SUCCESS;
        $response->message = "";
        $response->data = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $response;
    }

    /**
     * @param \Exception $ex
     * @return GenericResponse
     */
    public static function encodeException(\Exception $ex) {
        $response = new GenericResponse();
        $code = $ex->getCode();
        $response->code = $code === static::RESPONSE_SUCCESS ? 0 : $code;
        $response->message = $ex->getMessage();
        $response->data = "";
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
            throw new GenericInvokeException("Missing Service \"$serviceName\"");
        }

        $paramSpec = $classSpec->getInputStructSpec($methodName);
        if ($paramSpec === null) {
            throw new GenericInvokeException("Missing Service Method \"$methodName\"");
        }

        $expectedParamNum = count($paramSpec);
        if ($expectedParamNum > 0) {
            $params = static::parseMapParams($params); // map params
            // $params = static::parseListParams($params, $paramTypes); // list params

            $realParamNum = count($params);
            $requiredParamsNum = static::getRequiredParamsNum($serviceName, $methodName);
            // assert($requiredParamsNum <= $expectedParamNum);
            if ($realParamNum < $requiredParamsNum) {
                throw new GenericInvokeException("Too few arguments to $serviceName::$methodName, $realParamNum passed  and at least $requiredParamsNum expected");
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
            $args[] = json_decode($rawArg, true, 512, JSON_BIGINT_AS_STRING);
        }
        return $args;
    }
    */

    /**
     * 处理 map<string, json> 类型参数
     * @param string $rawArgs
     * @return array
     * @throws GenericInvokeException
     */
    private static function parseMapParams($rawArgs)
    {
        $args = json_decode($rawArgs, true, 512, JSON_BIGINT_AS_STRING);
        if (!is_array($args)) {
            throw new GenericInvokeException("Invalid generic request parameters");
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
                $arguments[$paramName] = static::parseSpec($item, $rawArgs[$paramName], $pos);
            } else if (isset($defaultValues[$pos])) {
                $arguments[$paramName] = $defaultValues[$pos];
            } else {
                throw new GenericInvokeException("Missing method parameter \"$paramName\"");
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
                throw new GenericInvokeException("Missing service implement method \"$appImplClassName::$methodName\"");
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
        $appNamespace = Application::getInstance()->getNamespace();
        $appImplClassName = $appNamespace . Nova::removeNovaNamespace($serviceName);
        if (!class_exists($appImplClassName)) {
            throw new GenericInvokeException("Missing service implement class \"$appImplClassName\"");
        }
        return $appImplClassName;
    }

    /**
     * 根据spec递归解析变量
     * @param array $specItem
     * @param mixed$rawValue
     * @param int $pos
     * @return mixed
     * @throws GenericInvokeException
     */
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

    /**
     * 清除返回值中无用的 _TSPEC 属性
     * @param array $specItem
     * @param $result
     */
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