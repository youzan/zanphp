<?php
/**
 * Abs TSpecification
 * User: moyo
 * Date: 9/15/15
 * Time: 3:23 PM
 */

namespace Zan\Framework\Nova\Foundation;

use Zan\Framework\Nova\Foundation\Traits\ServiceSpecManager;

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