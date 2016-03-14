<?php

namespace Zan\Framework\Network\Http\Request;

use Generator;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Network\Contract\Response;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Domain\Controller;
use Zan\Framework\Network\Http\Exception\InvalidRoute;
use Zan\Framework\Network\Http\Filter\FilterChain;

class RequestProcessor {

    private $context;
    private $request;
    private $response;
    private $filterChain;
    private $appNamespace = 'Zanhttp';

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->context  = new context();
        $this->filterChain = FilterChain::instance();
    }

    public function run($route)
    {
        $controller = $this->createController($route);

        if (!($controller instanceof Controller)) {
            throw new InvalidRoute('Not found controller:'.$controller);
        }
        $action = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new InvalidRoute('Class does not exist method '. get_class($controller).'::'.$action);
        }
        $this->doPreFilter();
        $result = $controller->$action();
        if ($result instanceof Generator) {
            $task = new Task($result);
            $task->run();
        }
        $this->doPostFilter();
    }

    private function doPreFilter()
    {
        $this->filterChain->doFilter($this->request, $this->response, $this->context);
    }

    private function doPostFilter()
    {
        $this->filterChain->setStepToPost();
        $this->filterChain->doFilter($this->request, $this->response, $this->context);
    }

    private function createController($route)
    {
        $module    = ucwords($route['module']);
        $className = ucwords($route['controller']);

        if (!preg_match('%^[A-Z][a-zA-Z][a-z0-9]*$%', $className)) {
            return null;
        }
        $className  = str_replace(' ', '', $className);
        $controller = ltrim($this->appNamespace . '\\' . $module . '\\Controller\\'. $className);

        if (!class_exists($controller)) {
            return null;
        }
        return new $controller($this->request, $this->response);
    }

}