<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Coroutine\Scheduler;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Exception\System\ExitException;
use Zan\Framework\Foundation\Exception\System\FileNotFound;

class Server implements \Zan\Framework\Network\Contract\Server {

    public $server = null;

    private $scheduler;

    public function __construct()
    {
        $httpConf = Config::get('http_server');
        $this->server = new \swoole_http_server($httpConf['host'], $httpConf['port']);
        $this->server->set($httpConf);
        $this->server->on('Request', [$this, 'onRequest']);
    }

    public function run($command)
    {
        if (!$command) {
            $this->start();
        }
        $func = strtolower($command);
        if (!method_exists($this, $func))
            throw new ExitException("Http server command not found: $func");

        $this->{$func}();
    }

    public function onRequest(\swoole_http_request $req, \swoole_http_response $resp)
    {
        try {
            $request = (new Request($req));
            $request->setGlobalVar();

            list($routes, $params) = (new Router($request))->parse();

            $result = (new \Application())->runAction($routes, $params);

            $html = new task($this->runAction($routes, $params));
            $resp->write($html);
        }
        catch (\Exception $e) {
        }
        $resp->end();
    }

    private function runAction($routes, $params)
    {
        $controller = $routes['module'].'_'.$routes['controller'].'Controller';
        $action = $routes['action'];

        if (!class_exists($controller) || !($controller instanceof BaseController)) {
            throw new FileNotFound('Class not found!');
        }
        if (!method_exists($controller, $action)) {
            throw new FileNotFound('function not found!');
        }
        $class = new $controller();

        if (method_exists($class, 'beforeAction')) {
            $class->beforeAction();
        }
        $result = $class->{$action}();

        if (method_exists($class, 'afterAction')) {
            $class->afterAction();
        }
        return $result;
    }

    public function start()
    {
        $this->server->start();
    }

    public function stop()
    {
        // TODO: Implement stop() method.
    }

    public function reload()
    {
        // TODO: Implement reload() method.
    }
}