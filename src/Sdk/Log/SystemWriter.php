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

    public function __construct($path)
    {
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }
        $this->path = $path;
    }

    public function init()
    {
        $connectionConfig = 'syslog.' . $this->path;
        (yield ConnectionManager::getInstance()->get($connectionConfig));
    }

    public function write($log)
    {
        var_dump('SystemWriter', $log);
        // TODO: Implement write() method.
    }

    public function execute(callable $callback)
    {
        // TODO: Implement execute() method.
    }
}
