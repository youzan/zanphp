<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as SwooleServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Time;

class RequestHandler {
    private $swooleServer = null;
    private $context = null;
    private $fd = null;
    private $fromId = null;


    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        $this->swooleServer = $swooleServer;
        $this->fd = $fd;
        $this->fromId = $fromId;

        $this->doRequest($data);
    }

    private function doRequest($data)
    {
        $request = new Request($this->fd, $this->fromId, $data);
        $response = new Response($this->swooleServer, $request);

        try {
            $this->context->set('request_time', Time::stamp());
            $request_timeout = Config::get('server.request_timeout');
            $this->context->set('request_timeout', $request_timeout);

            $request->decode();
            if ($request->getIsHeartBeat()) {
                $this->swooleServer->send($this->fd, $data);
                \Zan\Framework\Network\Server\Monitor\Worker::instance()->reactionRelease();
                return;
            }
            
            $requestTask = new RequestTask($request, $response, $this->context);
            $coroutine = $requestTask->run();
            Task::execute($coroutine, $this->context);
        } catch(\Exception $e) {
            \Zan\Framework\Network\Server\Monitor\Worker::instance()->reactionRelease();
            //TODO: 格式化exception输出
            if (Debug::get()) {
                var_dump($e);
            }
            $response->sendException($e);
            return;
        }
    }

}
