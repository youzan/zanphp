<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/14
 * Time: 11:47
 */

namespace Zan\Framework\Network\Http;

use RuntimeException;
use Mockery\CountValidator\Exception;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\Http\Request\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class Dispatcher {
    public function dispatch(Request $request, Context $context)
    {
        $route = $request->getRoute();
        $route = $this->parseRoute($route);

        $controller = $route['controller'];
        if(!class_exists($controller)) {
            throw new RuntimeException("controller:{$controller} not found!");
        }

        $controller = new $controller($request, $context);
        $action = $route['action'];
        if(!is_callable([$controller, $action])) {
            throw new RuntimeException("action:{$action} is not callable in controller:{$controller}!");
        }

        yield $controller->$action();
    }

    private function parseRoute($route)
    {
        $route = trim($route, ' /');
        $parts = explode('/', $route);

        $action = array_pop($parts);

        $parts = array_map('ucfirst', $parts);
        $controller = join('\\', $parts);

        $app = Application::getInstance();
        $controller = $app->getNamespace() . 'Controller\\' .  $controller . 'Controller';

        return [
            'controller' => $controller,
            'action' => $action
        ];
    }
}