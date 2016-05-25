<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:55
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class FileLogger extends BaseLogger implements Async
{

    private $callback;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->config['path'] = $this->getLogPath($this->config);
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }

    private function getLogPath($config)
    {
        $logBasePath = '';
        $path = ltrim($config['path'], '/');
        if ($config['factory'] === 'log') {
            $logBasePath = Config::get('path.log');
        } else if ($config['factory'] === 'file') {
            $logBasePath = '/';
        }
        $path = $logBasePath . $path;

        return $path;
    }

    public function write($log)
    {
        $path = $this->config['path'];
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }

        $callback = $this->config['async'] ? [$this, 'ioReady'] : null;
        swoole_async_write($path, $log, -1, $callback);
        if (null === $callback) {
            $this->ioReady();
        }
    }

    public function ioReady()
    {
        call_user_func($this->callback, true);
    }

}
