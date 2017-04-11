<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/17
 * Time: 23:55
 */

namespace Zan\Framework\Network\Tcp;

use Kdt\Iron\Nova\Nova;
use swoole_server as SwooleServer;
use Zan\Framework\Contract\Network\Response as BaseResponse;
use Zan\Framework\Network\Exception\GenericInvokeException;

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
    }

    /**
     * @param $e \Exception
     */
    public function sendException($e)
    {
        $this->exception = $e->getMessage();
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();

        // 泛化调用不透传任何异常, 直接打包发送
        if ($this->request->isGenericInvoke()) {
            return $this->send($e);
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
    }

    public function send($content)
    {
        $serviceName = $this->request->getServiceName();
        $novaServiceName = $this->request->getNovaServiceName();
        $methodName  = $this->request->getMethodName();

        if ($this->request->isGenericInvoke()) {
            $content = GenericRequestCodec::encode(
                $this->request->getGenericServiceName(),
                $this->request->getGenericMethodName(), $content);
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

            $msg_size = unpack("Nmsg_size", substr($outputBuffer, 0, 4))["msg_size"];
            $real_size = strlen($outputBuffer);
            if ($msg_size !== $real_size) {
                sys_echo("msg_size=$msg_size, real_size=$real_size");
            }

            $swooleServer = $this->getSwooleServer();
            $send_len = $swooleServer->send(
                $this->request->getFd(),
                $outputBuffer
            );

            if ($send_len !== $real_size) {

            }
        }
    }
    

}
