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
use Zan\Framework\Network\Connection\ConnectionManager;

class SystemWriter implements LogWriter, Async
{

    private $path;
    private $conn;
    private $callback;
    private $connectionConfig;

    public function __construct($path)
    {
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }
        $this->path = $path;
        $this->connectionConfig = 'syslog.' . str_replace('/', '', $this->path);
    }

    public function init()
    {
        $this->conn = (yield ConnectionManager::getInstance()->get($this->connectionConfig));
    }

    public function write($log)
    {
        var_dump('SystemWriter', $log, $this->conn);
        yield $this->conn->send($log);
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }
}
