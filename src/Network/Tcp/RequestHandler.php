<?php

namespace Zan\Framework\Network\Tcp;

use \swoole_server as SwooleServer;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\DesignPattern\Context;

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
//        $serviceName = $methodName = null;
//        $remoteIP = $remotePort = null;
//        $seqNo = $novaData = null;
//        $attachData = $execResult = null;
//        $reqState = null;

//        if (nova_decode($data, $serviceName, $methodName,
//                $remoteIP, $remotePort, $seqNo, $attachData, $novaData)) {
//
//            if('com.youzan.service.test' === $serviceName and 'ping' === $methodName) {
//                $this->swooleServer->send($this->fd, $data);
//                return;
//            }
//
//            $request = new Request($serviceName, $methodName, $novaData);
//            $request->setFd($this->fd)
//                    ->setRemote($remoteIP, $remotePort)
//                    ->setFromId($this->fromId)
//                    ->setSeqNo($seqNo)
//                    ->setAttachData($attachData);
//
//            $response = new Response($this->swooleServer, $request);
//
//
//        }

        $request = new Request($this->fd, $this->fromId, $data);
        $response = new Response($this->swooleServer, $request);

        try {
            $request->decode();

            if ($request->getIsHeartBeat()) {
                $this->swooleServer->send($this->fd, $data);
                return;
            }
        } catch(\Exception $e) {
            //TODO: send TApplicationException because decode failed
            return;
        }

        try {
            $requestTask = new RequestTask($request, $response, $this->context);
            $coroutine = $requestTask->run();
            Task::execute($coroutine, $this->context);
        } catch(\Exception $e) {
            //TODO: send bizException
            $response->sendException($e);
        }
    }


}
