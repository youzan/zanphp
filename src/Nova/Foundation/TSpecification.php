<?php

namespace Kdt\Iron\Nova\Foundation;

use Kdt\Iron\Nova\Foundation\Traits\ServiceSpecManager;

abstract class TSpecification
{
    /**
     * Spec mgr
     */
    use ServiceSpecManager;

    /**
     * @var string
     */
    protected $serviceName = 'com.youzan.gateway';

    /**
     * @return string
     */
    final public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @return array
     */
    final public function getServiceMethods()
    {
        return array_keys($this->inputStructSpec);
    }
}