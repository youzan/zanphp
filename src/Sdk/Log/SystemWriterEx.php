<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Network\Common\TcpClientEx;
use Zan\Framework\Network\Connection\ConnectionEx;


class SystemWriterEx implements LogWriter
{
    private $conn;

    public function __construct(ConnectionEx $conn)
    {
        $this->conn = $conn;
    }

    public function write($log)
    {
        try {
            $tcpClient = new TcpClientEx($this->conn);
            yield $tcpClient->send($log);
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }
}