<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/6/12
 * Time: 下午3:32
 */

namespace Zan\Framework\Network\Common;


use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Zan\Framework\Network\Connection\ConnectionEx;

class TcpClientEx implements Async
{
    /**
     * @var ConnectionEx
     */
    private $connEx;

    /**
     * @var \swoole_client
     */
    private $sock;

    /**
     * @var callable
     */
    private $callback;

    private $hasRecv;

    private $config;

    public function __construct(ConnectionEx $conn)
    {
        $this->connEx = $conn;
        $this->sock = $conn->getSocket();
        $this->config = $conn->getConfig();

        if (isset($this->config['hasRecv']) && $this->config['hasRecv'] === false) {
            $this->hasRecv = false;
        } else {
            $this->hasRecv = true;
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    // TODO 修改
    private function sendWithRecv($data)
    {
        $sendTimeout = isset($this->config["timeout"]) ? $this->config["timeout"] : 3000;

        $this->sock->on("timeout", ...);
        $this->sock->setSendTimeout($sendTimeout);
        $sent = $this->sock->sendWithCallback($data, [$this, "recv"]);

        if ($sent === false) {
            $this->connEx->close();
            throw new NetworkException("tcp send fail");
        }

        yield $this;
    }

    // TODO 修改
    private function sendWithoutRecv($data)
    {
        // 如果对面是flume 不回复消息, 回调不会触发, 不能在回调里头release
        $sent = $this->sock->sendWithCallback($data, function() { });

        if ($sent === false) {
            $this->connEx->close();
            throw new NetworkException("tcp send fail");
        } else {
            $this->connEx->release();
        }

        yield;
    }

    public function send($data)
    {
        if ($this->hasRecv) {
            yield $this->sendWithRecv($data);
        } else {
            yield $this->sendWithoutRecv($data);
        }
    }

    public function recv($data)
    {
        if (false === $data || '' == $data) {
            $this->connEx->close();
            $ex = new NetworkException(socket_strerror($this->sock->errCode), $this->sock->errCode);
            call_user_func($this->callback, $data, $ex);
        } else {
            $this->connEx->release();
            call_user_func($this->callback, $data);
        }
    }
}