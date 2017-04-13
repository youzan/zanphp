<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/4/19
 * Time: 上午10:19
 */

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Foundation\Core\RunMode;

class InitializeSyslog implements Bootable
{
    // flume
    const SYSLOG_ONLINE = ["127.0.0.1", 5140];
    const SYSLOG_DEV = ["10.9.65.239", 5140];

    public function bootstrap(Application $app)
    {
        Config::set('log.zan_framework', 'syslog://info/zan_framework?module=soa-framework');

        $logConf = [
            'engine'=> 'syslog',
            'timeout' => 5000,
            'persistent' => true,
            'pool' => [
                'keeping-sleep-time' => 10000,
                'init-connection' => 1,
                'maximum-connection-count' => 3,
                'minimum-connection-count' => 1,
            ],
        ];

        $host = Config::get("zan.syslog.host", null);
        $port = Config::get("zan.syslog.port", 5140);

        if ($host === null) {
            if (RunMode::isOnline()) {
                list($host, $port) = static::SYSLOG_ONLINE;
            } else {
                list($host, $port) = static::SYSLOG_DEV;
            }
        }

        $logConf['host'] = $host;
        $logConf['port'] = $port;

        Config::set('connection.syslog.zan_framework', $logConf);
    }
} 