<?php
/**
 * Spec mgr (for api)
 * User: moyo
 * Date: 9/17/15
 * Time: 8:03 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Foundation\Traits;

trait ApiSpecManager
{
    /**
     * @var array
     */
    protected $inputStructSpec = [];

    /**
     * @var array
     */
    protected $outputStructSpec = [];

    /**
     * @var array
     */
    protected $exceptionStructSpec = [];

    /**
     * @param $method
     * @return array
     */
    public function getInputStructSpec($method)
    {
        return isset($this->inputStructSpec[$method]) ? $this->inputStructSpec[$method] : null;
    }

    /**
     * @param $method
     * @return array
     */
    public function getOutputStructSpec($method)
    {
        return isset($this->outputStructSpec[$method]) ? $this->outputStructSpec[$method] : null;
    }

    /**
     * @param $method
     * @return array
     */
    public function getExceptionStructSpec($method)
    {
        return isset($this->exceptionStructSpec[$method]) ? $this->exceptionStructSpec[$method] : null;
    }
}