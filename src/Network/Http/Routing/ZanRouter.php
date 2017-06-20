<?php
namespace Zan\Framework\Network\Http\Routing;

use Zan\Framework\Network\Http\Request\Request;

class ZanRouter implements IRouter
{
    public function dispatch(Request $request)
    {
        $separator = "/";
        $parts = array_filter(explode($separator, trim($request->getRoute(), $separator)));
        $actionName = array_pop($parts);
        $controllerName = array_pop($parts);
        $moduleName = join($separator, $parts);
        return [$moduleName, $controllerName, $actionName];
    }
}