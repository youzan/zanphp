<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;

class InitializeSyslog implements Bootable
{
    public function bootstrap(Application $app)
    {
        $uri = Config::get('zan_syslog.uri');
        if (empty($uri)) {
            return;
        }

        Config::set('log.zan_framework', $uri);
        $host = Config::get("zan_syslog.host", "127.0.0.1");
        $port = Config::get("zan_syslog.port", 5140);

        $logConf = [
            'engine'=> 'syslog',
            'host' => $host,
            'port' => $port,
            'timeout' => 5000,
            'persistent' => true,
            'pool' => [
                'keeping-sleep-time' => 10000,
                'init-connection' => 1,
                'maximum-connection-count' => 3,
                'minimum-connection-count' => 1,
            ],
        ];
        Config::set('connection.syslog.zan_framework', $logConf);
    }
} 