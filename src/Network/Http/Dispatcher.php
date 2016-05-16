<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/14
 * Time: 11:47
 */

namespace Zan\Framework\Network\Http;

use RuntimeException;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class Dispatcher
{
    public function dispatch(Request $request, Context $context)
    {
        $controllerName = $context->get('controller_name');
        $action = $context->get('action_name');

        $controller = $this->getControllerClass($controllerName);
        if(!class_exists($controller)) {
            throw new RuntimeException("controller:{$controller} not found");
        }

        $controller = new $controller($request, $context);
        if(!is_callable([$controller, $action])) {
            throw new RuntimeException("action:{$action} is not callable in controller:" . get_class($controller));
        }

        if(method_exists($controller,'init')){
            yield $controller->init();
        }
        yield $controller->$action();
    }

    private function getControllerClass($controllerName)
    {
        $parts = array_filter(explode('/', $controllerName));
        $controllerName = join('\\', array_map('ucfirst', $parts));
        $app = Application::getInstance();
        return $app->getNamespace() . 'Controller\\' .  $controllerName . 'Controller';
    }
}