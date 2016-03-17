<?php
/**
 * Hijacks - Nova ping
 * User: moyo
 * Date: 1/22/16
 * Time: 1:43 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Transport\Hijack\Component;

use Zan\Framework\Network\Tcp\Nova\Transport\Hijack\Framework;

class Ping extends Framework
{
    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return bool
     */
    public function matchRequest($serviceName, $methodName, $thriftBIN)
    {
        return $serviceName == 'com.youzan.service.test' && $methodName == 'ping';
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return mixed
     */
    public function makeResponse($serviceName, $methodName, $thriftBIN)
    {
        return $thriftBIN;
    }
}