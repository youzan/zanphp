<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class FileWriter implements LogWriter, Async
{
    private $callback;
    private $path;
    private $dir;
    private $async;

    public function __construct($path, $async = true)
    {
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }

        $this->path = $path;
        $this->dir = dirname($this->path);
        $this->async = $async;
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    public function write($log)
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
            chmod($this->dir, 0755);
        }

        $callback = $this->async ? [$this, 'ioReady'] : null;
        swoole_async_write($this->path, $log, -1, $callback);

        yield $this;
    }

    public function ioReady()
    {
        if (!$this->callback) {
            return;
        }
        call_user_func($this->callback, true);
    }

}
