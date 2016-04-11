<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Core\Config;

class ClientRouter
{
    /**
     * @param $api
     * @return array
     */
    public static function lookup($api)
    {
        $javaApiConfig = Config::get('services.java');
        $phpApiConfig = Config::get('services.php');

        if (isset($javaApiConfig[$api])) {
            return $javaApiConfig[$api];
        } else {
            return $phpApiConfig;
        }
    }
}