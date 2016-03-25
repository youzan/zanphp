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

    private $attachmentContent = '{}';
    private $processorExceptionB64 = 'gAEAAwAAABBzZXJ2ZXIucHJvY2Vzc29yAAAAAAsAAQAAABpzZXJ2ZXIucHJvY2Vzc29yLmV4Y2VwdGlvbggAAgAAAAAA';
    private $processTitlePrefix = 'php-nova: ';

    public function __construct()
    {
        $this->context = new Context();
    }

    public function handle(SwooleServer $swooleServer, $fd, $fromId, $data)
    {
        $this->swooleServer = $swooleServer;
        $this->fd = $fd;
        $this->fromId = $fromId;

        return $this->doReuest($data);
    }

    private function doReuest($data)
    {
        $serviceName = $methodName = null;
        $remoteIP = $remotePort = null;
        $seqNo = $novaData = null;
        $attachData = $execResult = null;
        $outputBuffer = $sendState = $reqState = null;

        try {
            if (nova_decode($data, $serviceName, $methodName,
                    $remoteIP, $remotePort, $seqNo, $attachData, $novaData)) {

                $request = new Request($serviceName, $methodName, $novaData);
                $request->setRemote($remoteIP, $remotePort)
                        ->setFromId($this->fromId)
                        ->setSeqNo($seqNo)
                        ->setAttachData($attachData);

                $response = new Response($this->swooleServer, $this->fd);

                $requestTask = new RequestTask($request, $response, $this->context);
                $coroutine = $requestTask->run();

                Task::execute($coroutine, $this->context);
            }
        } catch (\Exception $e) {
            if ($seqNo) {
                $execResult = base64_decode($this->processorExceptionB64);
                if (nova_encode($serviceName, $methodName, $remoteIP,
                            $remotePort, $seqNo, $this->attachmentContent,
                            $execResult, $outputBuffer)) {
                    $sendState = $this->swooleServer->send($this->fd, $outputBuffer);
                }
            }
        }
    }


}