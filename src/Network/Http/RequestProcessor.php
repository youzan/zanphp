<?php
/**
 * @author hupp
 * create date: 16/01/15
 */

namespace Zan\Framework\Network\Http;

use Generator;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Domain\Controller;
use Zan\Framework\Network\Http\Exception\InvalidRoute;
use Zan\Framework\Network\Http\Filter\FilterChain;
use Zan\Framework\Test\Foundation\Coroutine\Context;

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
        $action = $this->createAction($route);

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
        $module = $this->getModule($route['module']);

        if (!isset($route['controller'])) {
            throw new InvalidRoute('Invalid request.');
        }
        $className  = ucwords($route['controller']);
        $className  = str_replace(' ', '', $className);
        $controller = ltrim($this->appNamespace . '\\Controller\\'. $module . '\\' . $className);

        if (!class_exists($controller)) {
            return $className;
        }
        return new $controller($this->request, $this->response);
    }

    private function createAction($route)
    {
        if (!isset($route['method'])) {
            throw new InvalidRoute('Invalid request.');
        }
        return ucwords($route['action']);
    }

    private function getModule($module = [])
    {
        foreach ($module as $index => $name) {
            $module[$index] = ucwords($name);
        }
        return join('\\', $module);
    }

}