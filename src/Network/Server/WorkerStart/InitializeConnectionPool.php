<?php

namespace Zan\Framework\Network\Server\WorkerStart;

use Zan\Framework\Network\Tcp\Server;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionInitiator;

class InitializeConnectionPool
{
    /**
     * @param
     */
    public function bootstrap(Server $server)
    {
        $config = Config::get('connection');
        ConnectionInitiator::getInstance()->init($config);
    }
}