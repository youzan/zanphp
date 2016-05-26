<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:51
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Contract\Async;

class SystemLogger extends BaseLogger implements Async
{

    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function execute(callable $callback)
    {
        // TODO: Implement execute() method.
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function format($level, $message, $context)
    {
        // TODO: Implement format() method.
    }

    public function write($log)
    {
        // TODO: Implement write() method.
    }
    
}
