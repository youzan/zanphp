<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\Driver\Syslog;

class SystemWriter implements LogWriter
{
    private $conn;

    public function __construct($conn)
    {
        if (!$conn instanceof Syslog) {
            throw new InvalidArgumentException('$conn master be instanceof Syslog.');
        }
        $this->conn = $conn;
    }

    public function write($log)
    {
        yield $this->conn->send($log);
    }
}
