<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/27
 * Time: 下午3:36
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\ConnectionManager;

class SystemWriter implements LogWriter
{

    public function __construct($path)
    {
        if (!$path) {
            throw new InvalidArgumentException('Path not be null');
        }
        $connectionConfig = 'syslog.' . $path;
        (yield ConnectionManager::getInstance()->get($connectionConfig));
    }

    public function write($log)
    {
        // TODO: Implement write() method.
    }
}
