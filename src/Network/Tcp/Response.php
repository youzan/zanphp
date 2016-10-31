<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Com\Youzan\Test\Service\GenericException;
use Kdt\Iron\Nova\Nova;
use swoole_server as SwooleServer;
use Zan\Framework\Contract\Network\Response as BaseResponse;

class Response implements BaseResponse {
    /**
     * @var SwooleServer
     */
    private $swooleServer;
    /**
     * @var Request
     */
    private $request;
    private $exception;

    public function __construct(SwooleServer $swooleServer, Request $request)
    {
        $this->swooleServer = $swooleServer;
        $this->request = $request;
    }

    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function end($content='')
    {
        $this->send($content);

        //$this->swooleServer->close($this->request->getFd());
    }

    public function sendException($e)
    {
        $this->exception = $e->getMessage();
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();

        if ($this->request->isGenericInvoke()) {
            if (!($e instanceof GenericException)) {
                /* @var $e \Exception */
                $e = new GenericException($e->getMessage(), $e->getCode(), $e); // TODO metaData
            }
        }

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

//        $this->swooleServer->close($this->request->getFd());
    }

    public function send($content)
    {
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();

        if ($this->request->isGenericInvoke()) {
            $content = GenericRequestUtils::encode($this->request->getGenericServiceName(), $this->request->getGenericMethodName(), $content);
        }

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
