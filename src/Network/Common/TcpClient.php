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

    public function __construct(Connection $conn)
    {
        $this->_conn = $conn;
        $this->_sock = $conn->getSocket();
        $this->_conn->setClientCb([$this, 'recv']);
    }

    public function execute(callable $callback)
    {
        $this->_callback = $callback;
    }

    public function recv($data) 
    {
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
            throw new NetworkException(socket_strerror($this->_sock->errCode), $this->_sock->errCode);
        }
        yield $this;
    }
}