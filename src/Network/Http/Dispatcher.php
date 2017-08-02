<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Network\Http\Exception\PageNotFoundException; 

class Dispatcher
{
    public function dispatch(Request $request, Context $context)
    {
        $controllerName = $context->get('controller_name');
        $action = $context->get('action_name');
        $args   = $context->get('action_args');

        $controller = $this->getControllerClass($controllerName);
        if(!class_exists($controller)) {
            throw new PageNotFoundException("controller:{$controller} not found");
        }

        $controller = new $controller($request, $context);
        if(!is_callable([$controller, $action])) {
            throw new PageNotFoundException("action:{$action} is not callable in controller:" . get_class($controller));
        }
        yield call_user_func_array([$controller,$action],$args);
    }

    private function getControllerClass($controllerName)
    {
        $parts = array_filter(explode('/', $controllerName));
        $controllerName = join('\\', array_map('ucfirst', $parts));
        $app = Application::getInstance();
        $controllerRootNamespace = Config::get('controller_mapping.root_namespace', $app->getNamespace());
        return $controllerRootNamespace . 'Controller\\' .  $controllerName . 'Controller';
    }
}
