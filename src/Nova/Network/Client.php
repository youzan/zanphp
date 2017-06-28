<?php

namespace Kdt\Iron\Nova\Network;

use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TMessageType;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Exception\ProtocolException;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Driver\NovaClient;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Log\Log;
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\DebuggerTrace;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Sdk\Trace\TraceBuilder;

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
        $trace = null;
        $debuggerTrace = null;

        if (false === $data or '' == $data) {
            $exception = new NetworkException(
                socket_strerror($this->_sock->errCode),
                $this->_sock->errCode
            );

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

            /** @var Trace $trace */
            $trace = $ctx->get('trace');
            $debuggerTrace = $ctx->get('debugger_trace');

            $cb = $context->getCb();
            if ($serviceName === 'com.youzan.service.test' && $methodName === 'pong') {
                $this->pong($cb);
                return;
            }
            /* @var $packer Packer */
            $packer = $context->getPacker();
            $serverIp = long2ip($remoteIP) . ':' . $remotePort;

            if ($serviceName == $context->getReqServiceName()
                    && $methodName == $context->getReqMethodName()) {
                try {
                    $response = $packer->decode(
                        $thriftBIN,
                        $packer->struct($context->getOutputStruct(), $context->getExceptionStruct()),
                        Packer::CLIENT
                    );
                }
                catch (\Throwable $e) { }
                catch (\Exception $e) { }
                if (isset($e)) {
                    if (null !== $trace) {
                        if ($e instanceof TApplicationException) {
                            //只有系统异常上报异常信息
                            $trace->commit($e->getTraceAsString());
                        } else {
                            $trace->commit(Constant::SUCCESS);
                        }
                    }
                    if ($debuggerTrace instanceof DebuggerTrace) {
                        $debuggerTrace->commit("error", $e);
                    }
                    call_user_func($cb, null, $e);
                    return;
                } else {
                    $ret = isset($response[$packer->successKey])
                        ? $response[$packer->successKey]
                        : null;
                    if (null !== $trace) {
                        $trace->commit(Constant::SUCCESS);
                }
                    if ($debuggerTrace instanceof DebuggerTrace) {
                        $debuggerTrace->commit("info", $ret);
                    }
                call_user_func($cb, $ret);
                return;
                }
            } 
        } else {
            $exception = new ProtocolException('nova.decoding.failed ~[client:'.strlen($data).']');
            goto handle_exception;
        }

handle_exception:
        foreach (self::$_reqMap as $req) {
            if (null !== $trace) {
                $trace = $req->getTask()->getContext()->get('trace');
                $trace->commit(socket_strerror($this->_sock->errCode));
            }
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
        $serverIp = $localIp . ':' . $localPort;

        /** @var Trace $trace */
        $trace = (yield getContext('trace'));
        $attachment = [];

        if (null !== $trace) {
            $trace->transactionBegin(Constant::NOVA_CLIENT, $this->_serviceName . '.' . $method);
            $msgId = TraceBuilder::generateId();
            $trace->logEvent(Constant::REMOTE_CALL, Constant::SUCCESS, "", $msgId);
            $trace->setRemoteCallMsgId($msgId);
            if ($trace->getRootId()) {
                $attachment[Trace::TRACE_KEY]['rootId'] = $attachment[Trace::TRACE_KEY][Trace::ROOT_ID_KEY] = $trace->getRootId();
            }
            if ($trace->getParentId()) {
                $attachment[Trace::TRACE_KEY]['parentId'] = $attachment[Trace::TRACE_KEY][Trace::PARENT_ID_KEY] = $trace->getParentId();
            }
            $attachment[Trace::TRACE_KEY]['eventId'] = $attachment[Trace::TRACE_KEY][Trace::CHILD_ID_KEY] = $msgId;
        }

        $debuggerTrace = (yield getContext("debugger_trace"));
        if ($debuggerTrace instanceof DebuggerTrace) {
            $name = $this->_serviceName . '.' . $method;
            $debuggerTrace->beginTransaction(Constant::NOVA_CLIENT, $name, $inputArguments);
        }

        $rpcCtx = (yield getRpcContext(null, []));
        $attachment = $attachment + $rpcCtx;

        if ($debuggerTrace instanceof DebuggerTrace) {
            $attachment[DebuggerTrace::KEY] = $debuggerTrace->getKey();
        }

        if ($attachment === [])
            $attachment = new \stdClass();
        else
            $attachment[Trace::TRACE_KEY] = json_encode($attachment[Trace::TRACE_KEY]);
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
            self::$seqTimerId[$_reqSeqNo] = Timer::after(self::$sendTimeout, function() use($debuggerTrace, $_reqSeqNo) {
                if ($debuggerTrace instanceof DebuggerTrace) {
                    $debuggerTrace->commit("warn", "timeout");
                }
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
        $traceId = '';
        if (null !== $trace) {
            $trace->commit($exception);
            $traceId = $trace->getRootId();
        }
        if ($debuggerTrace instanceof DebuggerTrace) {
            $debuggerTrace->commit("error", $exception);
        }

        if (Config::get('log.zan_framework')) {
            yield Log::make('zan_framework')->error($exception->getMessage(), [
                'exception' => $exception,
                'app' => Application::getInstance()->getName(),
                'language'=>'php',
                'side'=>'client',//server,client两个选项
                'traceId'=> $traceId,
                'method'=>$this->_serviceName.'.'.$method,
            ]);
        }

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