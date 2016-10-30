<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Test\Service\GenericException;
use Com\Youzan\Test\Service\GenericRequest;
use Zan\Framework\Contract\Network\Request as BaseRequest;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;

class Request implements BaseRequest {
    private $data;
    private $route;
    private $serviceName;
    private $novaServiceName;
    private $methodName;
    private $args;
    private $fd;

    private $remoteIp;
    private $remotePort;
    private $fromId;
    private $seqNo;
    private $attachData;
    private $isHeartBeat = false;

    private $isGenericInvoke = false;
    private $genericServiceName;
    private $genericMethodName;

    const GENERIC_SERVICE_PREFIX = 'com.youzan.test.service';

    public function __construct($fd, $fromId, $data)
    {
        $this->fd = $fd;
        $this->fromId = $fromId;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setFd($fd)
    {
        $this->fd = $fd;
    }

    public function getFd()
    {
        return $this->fd;
    }

    public function setRemote($ip, $port)
    {
        $this->remoteIp = $ip;
        $this->remotePort = $port;
    }

    public function setFromId($fromId)
    {
        $this->fromId = $fromId;
    }

    public function setSeqNo($seqNo)
    {
        $this->seqNo = $seqNo;
    }

    public function setAttachData($attachData)
    {
        $this->attachData = $attachData;
    }

    public function getAttachData()
    {
        return $this->attachData;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function getNovaServiceName()
    {
        return $this->novaServiceName;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getRemote()
    {
        return [
            'ip' =>$this->remoteIp,
            'port' => $this->remotePort,
        ];
    }

    public function getRemotePort()
    {
        return $this->remotePort;
    }

    public function getFromId()
    {
        return $this->fromId;
    }

    public function getSeqNo()
    {
        return $this->seqNo;
    }

    public function getIsHeartBeat()
    {
        return $this->isHeartBeat;
    }

    public function getGenericServiceName()
    {
        return $this->genericServiceName;
    }

    public function getGenericMethodName()
    {
        return $this->genericMethodName;
    }

    public function isGenericInvoke()
    {
        return $this->isGenericInvoke;
    }

    private function formatRoute()
    {
        $serviceName = ucwords($this->serviceName, '.');
        $this->novaServiceName = str_replace('.','\\',$serviceName);

        $path = '/'. str_replace('.', '/', $serviceName) . '/';
        $this->route = $path . $this->methodName;
    }

    private function decodeArgs()
    {
        $this->args = Nova::decodeServiceArgs(
            $this->novaServiceName,
            $this->methodName,
            $this->args
        );
    }

    public function decode() {
        $serviceName = $methodName = null;
        $remoteIP = $remotePort = null;
        $seqNo = $novaData = null;
        $attachData = $reqState = null;

        if (nova_decode($this->data, $serviceName, $methodName,
            $remoteIP, $remotePort, $seqNo, $attachData, $novaData)) {

            $this->serviceName = trim($serviceName);
            $this->methodName = trim($methodName);
            $this->args = $novaData;
            $this->remoteIp = $remoteIP;
            $this->remotePort = $remotePort;
            $this->seqNo = $seqNo;
            $this->attachData = $attachData;
            
            if('com.youzan.service.test' === $serviceName and 'ping' === $methodName) {
                $this->isHeartBeat = true;
//                echo "heartbeating ...\n";
                $data = null;
                nova_encode($this->serviceName, 'pong', $this->remoteIp, $this->remotePort, $this->seqNo, '', '', $data);
                return $data;
            }

            $this->isGenericInvoke = static::GENERIC_SERVICE_PREFIX
                === substr($serviceName, 0, strlen(static::GENERIC_SERVICE_PREFIX));
            if ($this->isGenericInvoke) {
                $this->decodeGeneric();
                return;
            }

            $this->formatRoute();
            $this->decodeArgs();
        } else {
            //TODO: throw TApplicationException
        }
    }

    private function decodeGeneric()
    {
        $this->novaServiceName = str_replace('.', '\\', ucwords($this->serviceName, '.'));
        $args = Nova::decodeServiceArgs($this->novaServiceName, $this->methodName, $this->args);

        if ($args[0] && $args[0] instanceof GenericRequest) {
            /* @var $genericRequest GenericRequest */
            $genericRequest = $args[0];

            static::genericRequestCheck($genericRequest);

            $this->genericServiceName = $genericRequest->serviceName;
            $this->genericMethodName = $genericRequest->methodName;
            $this->args = $genericRequest->methodParams;
            $this->route = '/'. str_replace('\\', '/', $this->genericServiceName) . '/' . $this->genericMethodName;

        } else {
            throw new GenericException("Invalid GenericRequest");
        }
    }

    private static function genericRequestCheck(GenericRequest $request)
    {
        $className = $request->serviceName;
        $methodName = $request->methodName;
        $params = $request->methodParams;

        if (!$className || !$methodName) {
            throw new GenericException("Invalid class or method");
        }

        $className = str_replace('.', '\\', ucwords($className, '.'));
        if (!class_exists($className)) {
            throw new GenericException("Missing proxy class \"$className\"");
        }
        $request->serviceName = $className;

        // 获取app中服务实现类, 以支持默认参数
        $appNamespace = Application::getInstance()->getNamespace();
        $appImplClassName = $appNamespace . Nova::removeNovaNamespace($className);
        if (!class_exists($appImplClassName)) {
            throw new GenericException("Missing app impl class \"$appImplClassName\"");
        }

        $class = new \ReflectionClass($appImplClassName);
        if (!$class->hasMethod($methodName)) {
            throw new GenericException("Missing method \"$methodName\"");
        }

        $method = $class->getMethod($methodName);
        if (!$method->isPublic() || $method->isAbstract()) {
            throw new GenericException("\"$method\" can not be accessed");
        }

        $paramsNum = $method->getNumberOfParameters();
        if ($paramsNum > 0) {
            if (!$params) {
                throw new GenericException("Missing parameters");
            }

            $params = json_decode($params, true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($params)) {
                throw new GenericException("Invalid parameters codec");
            }

            $requiredParamsNum = $method->getNumberOfRequiredParameters();
            if (count($params) < $requiredParamsNum) {
                throw new GenericException("Missing required parameters");
            }

            $request->methodParams = static::makeParameters($method, array_values($params));
        } else {
            $request->methodParams = [];
        }
    }

    private static function makeParameters(\ReflectionMethod $method, array $paramList)
    {
        $arguments = [];
        $requiredParamsNum = $method->getNumberOfRequiredParameters();
        foreach ($method->getParameters() as $pos => $parameter) {
            if ($pos < $requiredParamsNum) {

                $typeHint = $parameter->getClass();
                if ($typeHint) {
                    $propertiesArray = $paramList[$pos];
                    if (!is_array($propertiesArray)) {
                        throw new GenericException("Invalid parameter hinted by type: " . $typeHint->getName());
                    }
                    $arguments[$pos] = static::makeObjectByTypeHint($typeHint, $propertiesArray);
                } else {
                    $arguments[$pos] = $paramList[$pos];
                }

            } else {
                $arguments[$pos] = isset($paramList[$pos]) ? $paramList[$pos] : $parameter->getDefaultValue();
            }
        }

        return $arguments;
    }

    private static function makeObjectByTypeHint(\ReflectionClass $typeHint, array $propertiesArray)
    {
        $object = $typeHint->newInstanceWithoutConstructor();

        $publicProperties = $typeHint->getProperties(\ReflectionProperty::IS_PUBLIC);
        $staticProperties = $typeHint->getProperties(\ReflectionProperty::IS_STATIC);
        $properties = array_diff($publicProperties, $staticProperties);

        foreach ($properties as $property) {
            /* @var $property \ReflectionProperty */
            $propertyName = $property->getName();
            if (isset($propertiesArray[$propertyName])) {

                // TODO 依靠document中的type提示递归处理嵌套类型构造
                $propertyTypeHint = null;
                $propertyValue = $propertiesArray[$propertyName];
                if ($propertyTypeHint) {
                    if (is_array($propertyValue)) {
                        $propertyValue = static::makeObjectByTypeHint($propertyTypeHint, $propertyValue);
                    } else {
                        throw new GenericException("Invalid inner property");
                    }
                }

                // $property->setAccessible(true);
                $object->$propertyName = $propertyValue;
            }
        }

        return $object;
    }
}