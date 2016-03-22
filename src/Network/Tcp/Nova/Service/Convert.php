<?php
/**
 * Service convert (IO format)
 * User: moyo
 * Date: 9/25/15
 * Time: 2:49 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Service;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;

class Convert
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @param $data
     * @param $struct
     * @return array
     */
    public function inputArgsToFuncArray($data, $struct)
    {
        $pack = [];
        foreach ($struct as $pos => $config)
        {
            $pack[] = isset($data[$config['var']]) ? $data[$config['var']] : null;
        }
        return $pack;
    }
}