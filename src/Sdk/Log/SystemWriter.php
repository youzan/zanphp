<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/27
 * Time: 下午3:36
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\Driver\Syslog;

class SystemWriter implements LogWriter, Async
{
    private $conn;
    private $callback;

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

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

}
