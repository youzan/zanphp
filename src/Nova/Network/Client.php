<?php

namespace Kdt\Iron\Nova\Network;

use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Type\TMessageType;
use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Exception\ProtocolException;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Driver\NovaClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\Tcp\RpcContext;

class Client implements Async
{
    const DEFAULT_SEND_TIMEOUT = 3000;
    const MAX_NOVA_ATTACH_LEN = 30000; // nova header 总长度 0x7fff;

    /**
     * @var NovaClient
     */
    private $_conn;
    /**
     * @var \swoole_client
     */
    private $_sock;
    private $_serviceName;
    /**
     * @var ClientContext
     */
    private $_currentContext;
    private static $_reqMap = [];

    private static $_instance = null; // memory_leak

    private static $sendTimeout;

    private static $seqTimerId = [];

    final public static function getInstance(Connection $conn, $serviceName)
    {
        $key = spl_object_hash($conn) . '_' . $serviceName;
        if (!isset(static::$_instance[$key]) || null === static::$_instance[$key]) {
            static::$_instance[$key] = new self($conn, $serviceName);
            if (self::$sendTimeout === null) {
                self::$sendTimeout = Config::get("connection.nova.send_timeout", static::DEFAULT_SEND_TIMEOUT);
            }
        }
        return static::$_instance[$key];
    }

    public function __construct(Connection $conn, $serviceName)
    {
        $this->_conn = $conn;
        $this->_sock = $conn->getSocket();
        $this->_conn->setClientCb(function($data) {
            $this->recv($data);
        });
        $this->_serviceName = $serviceName;
    }

    public function execute(callable $callback, $task)
    {
        $this->_currentContext->setCb($callback);
        $this->_currentContext->setTask($task);
    }

    /**
     * @param $data
     * @throws NetworkException
     */
    public function recv($data) 
    {
        $exception = null;

        if (false === $data or '' == $data) {
            $exception = new NetworkException(socket_strerror($this->_sock->errCode),  $this->_sock->errCode );
            goto handle_exception;
        }

        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $attachData = $thriftBIN = null;
        if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $thriftBIN)) {
            if (isset(self::$seqTimerId[$seqNo])) {
                Timer::clearAfterJob(self::$seqTimerId[$seqNo]);
                unset(self::$seqTimerId[$seqNo]);
            }

            /** @var ClientContext $context */
            $context = isset(self::$_reqMap[$seqNo]) ? self::$_reqMap[$seqNo] : null;
            if (!$context) {
                throw new NetworkException("nova call timeout");
            }
            unset(self::$_reqMap[$seqNo]);

            /* @var $ctx \Zan\Framework\Utilities\DesignPattern\Context */
            $ctx = $context->getTask()->getContext();
            $rpcCtx = RpcContext::unpack($attachData);
            $rpcCtx->bindTaskCtx($ctx);

            $cb = $context->getCb();
            if ($serviceName === 'com.youzan.service.test' && $methodName === 'pong') {
                $this->pong($cb);
                return;
            }
            /* @var $packer Packer */
            $packer = $context->getPacker();

            if ($serviceName == $context->getReqServiceName() && $methodName == $context->getReqMethodName()) {
                try {
                    $response = $packer->decode(
                        $thriftBIN,
                        $packer->struct($context->getOutputStruct(), $context->getExceptionStruct()),
                        Packer::CLIENT
                    );
                } catch (\Exception $e) {
                    call_user_func($cb, null, $e);
                    return;
                }

                $ret = isset($response[$packer->successKey]) ? $response[$packer->successKey] : null;
                call_user_func($cb, $ret);
                return;
            } 
        } else {
            $exception = new ProtocolException('nova.decoding.failed ~[client:'.strlen($data).']');
            goto handle_exception;
        }

handle_exception:
        foreach (self::$_reqMap as $req) {
            $req->getTask()->sendException($exception);
        }

        $this->_conn->close();
    }


    /**
     * @param $method
     * @param $inputArguments
     * @param $outputStruct
     * @param $exceptionStruct
     * @return \Generator
     * @throws NetworkException
     * @throws ProtocolException
     */
    public function call($method, $inputArguments, $outputStruct, $exceptionStruct)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        $_reqSeqNo = nova_get_sequence();
        $_packer = Packer::newInstance();
        
        $context = new ClientContext();
        $context->setOutputStruct($outputStruct);
        $context->setExceptionStruct($exceptionStruct);
        $context->setReqServiceName($this->_serviceName);
        $context->setReqMethodName($method);
        $context->setReqSeqNo($_reqSeqNo);
        $context->setPacker($_packer);
        $context->setStartTime();

        $this->_currentContext = $context;
        
        $thriftBin = $_packer->encode(TMessageType::CALL, $method, $inputArguments, Packer::CLIENT);
        $sockInfo = $this->_sock->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;

        $attachment = (yield getRpcContext(null, []));
        if ($attachment === []) {
            $attachment = new \stdClass();
        }
        $_attachmentContent = json_encode($attachment);
        if (strlen($_attachmentContent) >= self::MAX_NOVA_ATTACH_LEN) {
            $_attachmentContent = '{"error":"len of attach overflow"}';
        }
        $context->setAttachmentContent($_attachmentContent);

        if (nova_encode($this->_serviceName, $method, $localIp, $localPort, $_reqSeqNo, $_attachmentContent, $thriftBin, $sendBuffer)) {
            $this->_conn->setLastUsedTime();
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                $exception = new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
                goto handle_exception;
            }

            self::$_reqMap[$_reqSeqNo] = $context;
            self::$seqTimerId[$_reqSeqNo] = Timer::after(self::$sendTimeout, function() use($_reqSeqNo) {
                unset(self::$_reqMap[$_reqSeqNo]);
                unset(self::$seqTimerId[$_reqSeqNo]);
            });

            yield $this;
            return;
        } else {
            $exception = new ProtocolException('nova.encoding.failed');
            goto handle_exception;
        }

handle_exception:
        throw $exception;
    }

    public function ping()
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        $_reqSeqNo = nova_get_sequence();
        $method = 'ping';
        $context = new ClientContext();
        $context->setReqServiceName($this->_serviceName);
        $context->setReqMethodName($method);
        $context->setReqSeqNo($_reqSeqNo);

        $this->_currentContext = $context;

        $sockInfo = $this->_sock->getsockname();
        $localIp = ip2long($sockInfo['host']);
        $localPort = $sockInfo['port'];
        $sendBuffer = null;

        if (nova_encode($this->_serviceName, $method, $localIp, $localPort, $_reqSeqNo, '', '', $sendBuffer)) {
            $this->_conn->setLastUsedTime();
            $sent = $this->_sock->send($sendBuffer);
            if (false === $sent) {
                throw new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
            }

            self::$_reqMap[$_reqSeqNo] = $context;
            Timer::after(self::$sendTimeout, function() use($_reqSeqNo) {
                unset(self::$_reqMap[$_reqSeqNo]);
            });

            yield $this;
        } else {
            throw new ProtocolException('nova.encoding.failed');
        }
    }

    public function pong($cb)
    {
        call_user_func($cb, true);
    }
}