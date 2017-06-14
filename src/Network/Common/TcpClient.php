<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Zan\Framework\Contract\Network\Connection;

class TcpClient implements Async
{
    /**
     * @var \Zan\Framework\Network\Connection\Driver\Tcp
     */
    private $conn;

    /**
     * @var \swoole_client
     */
    private $sock;

    /**
     * @var callable
     */
    private $callback;

    private $hasRecv = true;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->sock = $conn->getSocket();
        $config = $conn->getConfig();
        if (isset($config['hasRecv']) && $config['hasRecv'] === false) {
            $this->hasRecv = false;
        } else {
            $this->conn->setClientCb([$this, 'recv']);
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    public function recv($data)
    {
        $this->conn->release();
        if (false === $data or '' == $data) {
            throw new NetworkException(
                socket_strerror($this->sock->errCode),
                $this->sock->errCode
            );
        }
        call_user_func($this->callback, $data);
    }

    public function send($data)
    {
        $sent = $this->sock->send($data);
        if (false === $sent) {
            throw new NetworkException("tcp send fail");
        }

        if ($this->hasRecv) {
            yield $this;
        } else {
            $this->conn->release();
            yield;
        }
    }
}