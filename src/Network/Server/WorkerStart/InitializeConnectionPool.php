<?php

namespace Zan\Framework\Network\Server\WorkerStart;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Contract\Network\Bootable;

class InitializeConnectionPool implements Bootable
{
    public function bootstrap($server)
    {
        ConnectionInitiator::getInstance()->init('connection', $server);
    }
}