<?php
/**
 * Service struct detector
 * User: moyo
 * Date: 9/21/15
 * Time: 7:07 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Service;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;

class Finder
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Reflection
     */
    private $ref = null;

    /**
     * @var Objects
     */
    private $objects = null;

    /**
     * Detector constructor.
     */
    public function __construct()
    {
        $this->ref = Reflection::instance();
        $this->objects = Objects::instance();
    }

    /**
     * @param $serviceName
     * @return string
     */
    public function getServiceController($serviceName)
    {
        return $this->ref->getServiceController($serviceName);
    }

    /**
     * @param $serviceName
     * @return mixed
     */
    public function getServiceControllerInstance($serviceName)
    {
        return $this->objects->load($this->getServiceController($serviceName));
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getInputStruct($serviceName, $method)
    {
        $clientCN = $this->ref->getClientClass($serviceName);

        /**
         * @var \Zan\Framework\Network\Tcp\Nova\Foundation\TClient
         */
        $clientOJ = $this->objects->load($clientCN);

        $args = $clientOJ->getInputStructSpec($method);

        return $args;
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getOutputStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getServiceClass($serviceName);

        /**
         * @var \Zan\Framework\Network\Tcp\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getOutputStructSpec($method);

        return $args;
    }

    /**
     * @param $serviceName
     * @param $method
     * @return array
     */
    public function getExceptionStruct($serviceName, $method)
    {
        $serviceCN = $this->ref->getServiceClass($serviceName);

        /**
         * @var \Zan\Framework\Network\Tcp\Nova\Foundation\TService
         */
        $serviceCJ = $this->objects->load($serviceCN);

        $args = $serviceCJ->getExceptionStructSpec($method);

        return $args;
    }
}