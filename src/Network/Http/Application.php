<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/31
 * Time: 23:55
 */

class Application extends \Zan\Framework\Network\Contract\Application {

    private $controllerNamespace = 'app\\controllers';


    public function runAction($route, $params = [])
    {
        $controller = $this->createController($route, $params);
        $action = $route['action'];

        $controller->beforAction();

        if (!method_exists($controller, $action)) {
            throw new \Exception('....');
        }
        ////todo load PreFilter

        $result = $controller->$action();

        //todo load PostFilter

        $controller->afterAction();

        return $result;
    }

    public function createController($route, $params)
    {
        $module    = $route['module'];
        $className = $route['controller'];

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        $className = $module.'_'.str_replace(' ', '', ucwords(str_replace('-', ' ', $className))).'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . $className, '\\');

        if (!class_exists($className)) {
            throw new Exception('....');
        }
        if (!($className instanceof BaseController)) {
            throw new Exception('....');
        }
        return new $className;

    }

    public function init() {
        //parent::init();
    }




}