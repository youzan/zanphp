<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/26
 * Time: 上午11:46
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class FileWriter implements LogWriter, Async
{
    private $callback;
    private $path;
    private $async;

    public function __construct($path, $async = true)
    {
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }
        
        $this->path = $path;
        $this->async = $async;
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }

    public function write($log)
    {
        //TODO move to __construct
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }

        $callback = $this->async ? [$this, 'ioReady'] : null;
        swoole_async_write($this->path, $log, -1, $callback);
        
        if (null === $callback) {
            $this->ioReady();
        }
    }

    public function ioReady()
    {
        if (!$this->callback) {
            return;
        }
        call_user_func($this->callback, true);
    }

}
