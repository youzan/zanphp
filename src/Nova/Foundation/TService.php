<?php

namespace Kdt\Iron\Nova\Foundation;

use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Network\Client;
use Kdt\Iron\Nova\Service\Registry;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Connection\NovaClientConnectionManager;

abstract class TService
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var TSpecification
     */
    private $relatedSpec = null;

    /**
     * @return TSpecification
     */
    abstract protected function specificationProvider();

    /**
     * @param $method
     * @param $args
     * @return array
     */
    final public function getInputStructSpec($method, $args = [])
    {
        $spec = $this->getRelatedSpec()->getInputStructSpec($method);
        foreach ($args as $i => $arg)
        {
            $spec[$i + 1]['value'] = $arg;
        }

        return $spec;
    }

    /**
     * @param $method
     * @return array
     */
    final public function getOutputStructSpec($method)
    {
        return $this->getRelatedSpec()->getOutputStructSpec($method);
    }

    /**
     * @param $method
     * @return array
     */
    final public function getExceptionStructSpec($method)
    {
        return $this->getRelatedSpec()->getExceptionStructSpec($method, true);
    }

    /**
     * @param $method
     * @param $arguments
     * @return \Generator
     * @throws NetworkException
     * @throws \Kdt\Iron\Nova\Exception\ProtocolException
     * @throws \Zan\Framework\Foundation\Exception\System\InvalidArgumentException
     */
    final protected function apiCall($method, $arguments)
    {
        $domain = ""; // nova协议header中domain已经移除 !!!
        $serviceName = self::getNovaServiceName($this->getRelatedSpec()->getServiceName());
        $connection = (yield NovaClientConnectionManager::getInstance()->get(Registry::PROTO_NOVA, $domain, $serviceName, $method));
        if (!($connection instanceof Connection)) {
            throw new NetworkException('get nova connection error');
        }

        $client = Client::getInstance($connection, $serviceName);
        yield $client->call($method, $this->getInputStructSpec($method, $arguments), $this->getOutputStructSpec($method), $this->getExceptionStructSpec($method));
    }
    
    final public static function getNovaServiceName($specServiceName)
    {
        $nameArr = explode('.', $specServiceName);
        $className = array_pop($nameArr);
        $nameArr = array_map('lcfirst', $nameArr);
        $nameArr[] = $className;

        return join('.', $nameArr);
    }

    /**
     * @return TSpecification
     */
    final private function getRelatedSpec()
    {
        if (is_null($this->relatedSpec))
        {
            $this->relatedSpec = $this->specificationProvider();
        }
        return $this->relatedSpec;
    }
}