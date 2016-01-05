<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\ExitException;

class Server implements \Zan\Framework\Network\Contract\Server {

    public $server = null;

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

            $result = (new \Application())->run($routes, $params);
            $resp->write($result);
            $resp->end();
        }
        catch (\Exception $e) {
            $resp->status(500);
            $resp->end($e->getMessage() . "<hr />" . nl2br($e->getTraceAsString()));
        }
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