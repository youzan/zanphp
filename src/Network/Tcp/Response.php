<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Network\Tcp;

use Kdt\Iron\Nova\Nova;
use swoole_server as SwooleServer;
use Zan\Framework\Contract\Network\Response as BaseResponse;

class Response implements BaseResponse {
    /**
     * @var SwooleServer
     */
    private $swooleServer;
    private $request;

    public function __construct(SwooleServer $swooleServer, Request $request)
    {
        $this->swooleServer = $swooleServer;
        $this->request = $request;
    }

    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    public function end($content='')
    {
        $this->send($content);
    }

    public function sendException($e)
    {
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();
        $content = Nova::encodeServiceException($novaServiceName, $methodName, $e);

        $remote = $this->request->getRemote();
        $outputBuffer = '';
        if (nova_encode($serviceName,
            $methodName,
            $remote['ip'],
            $remote['port'],
            $this->request->getSeqNo(),
            $this->request->getAttachData(),
            $content,
            $outputBuffer)) {


            $swooleServer = $this->getSwooleServer();
            $swooleServer->send(
                $this->request->getFd(),
                $outputBuffer
            );
        }
    }

    public function send($content)
    {
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();
        $content = Nova::encodeServiceOutput($novaServiceName, $methodName, $content);

        $remote = $this->request->getRemote();
        $outputBuffer = '';
        if (nova_encode($serviceName,
            $methodName,
            $remote['ip'],
            $remote['port'],
            $this->request->getSeqNo(),
            $this->request->getAttachData(),
            $content,
            $outputBuffer)) {


            $swooleServer = $this->getSwooleServer();
            $sendState = $swooleServer->send(
                $this->request->getFd(),
                $outputBuffer
            );
        }
    }
    

}
