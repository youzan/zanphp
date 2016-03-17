<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/14
 * Time: 11:47
 */

namespace Zan\Framework\Network\Http;


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
        $controller = new $controller($request, $context);
        $action = $route['action'];

        yield $controller->$action();
    }

    private function parseRoute($route)
    {
        $route = trim($route, ' /');
        $parts = explode('/', $route);

        $action = array_pop($parts);
        $action = $this->parseAction($action);

        $parts = array_map('ucfirst', $parts);
        $controller = join('\\', $parts);

        $app = Application::getInstance();
        $controller = $app->getNamespace() . 'Controller\\' .  $controller . 'Controller';

        return [
            'controller' => $controller,
            'action' => $action['action'],
            'format' => $action['format'],
        ];
    }

    private function parseAction($action)
    {
        $arr = explode('.', $action);
        $ret = [
            'action'    => $arr[0],
        ];

        if(isset($arr[1])){
            $ret['format']  = $arr[1];
        } else {
            $ret['format']  = 'html';
        }

        return $ret;
    }

}