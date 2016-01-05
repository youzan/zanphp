<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/31
 * Time: 23:55
 */

use Zan\Framework\Network\Contract\Controller;
use Zan\Framework\Network\Exception\InvalidRoute;

class Application extends \Zan\Framework\Network\Contract\Application {

    private $controllerNamespace = 'app\\controllers';


    public function run($route, $params = [])
    {
        /* @var Controller */
        $controller = $this->createController($route, $params);

        if (!($controller instanceof Controller)) {
            throw new InvalidRoute('Invalid controller!');
        }
        return $controller->runAction($route['action'], $params);
    }

    public function createController($route, $params)
    {
        $module    = $route['module'];
        $className = $route['controller'];

        if (!preg_match('%^[a-z][a-z0-9]*$%', $className)) {
            return null;
        }
        $className = $module.'_'.str_replace(' ', '', ucwords($className)).'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . $className, '\\');

        if (!class_exists($className)) {
            return null;
        }
        return new $className();
    }


    public function init() {
        //parent::init();
    }


}