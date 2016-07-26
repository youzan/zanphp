<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 7/26/16
 * Time: 11:01
 */

namespace Zan\Framework\Utilities\Helper;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Foundation\Exception\ZanException;

trait FilterHelper
{
    private function getModules(Request $request)
    {
        static $store = [];
        $route = $request->getRoute();
        if (!isset($store[$route])) {
            $routeAry = explode('/', $route);
            if (2 > count($routeAry)) {
                throw new ZanException('Error route');
            }
            if (!isset($routeAry[2])) {
                $routeAry[2] = 'index';
            }

            $result['module'] = $routeAry[0];
            $result['controller'] = $routeAry[1];
            $result['action'] = $routeAry[2];
            $store[$route] = $result;
        }
        return $store[$route];
    }
}