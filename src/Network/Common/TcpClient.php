<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Contract\Async;
use Kdt\Iron\Nova\Exception\NetworkException;
use Kdt\Iron\Nova\Exception\ProtocolException;
use Zan\Framework\Contract\Network\Connection;

class TcpClient implements Async
{
    private $_conn;
    private $_sock;
    private $_callback;
    private $_hasRecv = true;

    public function __construct(Connection $conn)
    {
        $this->_conn = $conn;
        $this->_sock = $conn->getSocket();
        $config = $conn->getConfig();
        if (isset($config['hasRecv']) && $config['hasRecv'] === false) {
            $this->_hasRecv = false;
        } else {
            $this->_conn->setClientCb([$this, 'recv']);
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->_callback = $callback;
    }

    public function recv($data) 
    {
        $this->_conn->release();
        if (false === $data or '' == $data) {
            throw new NetworkException(
                socket_strerror($this->_sock->errCode),
                $this->_sock->errCode
            );
        }
        call_user_func($this->_callback, $data);
    }
    
    public function send($data)
    {
        $sent = $this->_sock->send($data);
        if (false === $sent) {
            throw new NetworkException("tcp send fail");
        }

        if (!$this->_hasRecv) {
            $this->_conn->release();
            yield;
        } else {
            yield $this;
        }
    }
}