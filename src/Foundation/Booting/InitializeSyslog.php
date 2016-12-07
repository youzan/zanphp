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
    public function bootstrap(Application $app)
    {
        Config::set('log.zan_framework', 'syslog://info/zan_framework?module=soa-framework');
        $runMode = RunMode::get();

        $logConf = [
            'engine'=> 'syslog',
            'port' => '5140',
            'timeout' => 5000,
            'persistent' => true,
            'pool' => [
                'keeping-sleep-time' => 10000,
                'init-connection' => 1,
                'maximum-connection-count' => 3,
                'minimum-connection-count' => 1,
            ],
        ];

        if ($runMode == 'pre' || $runMode == 'online') {
            $logConf['host'] = '127.0.0.1';
        } else {
            $logConf['host'] = '10.9.65.239';
        }

        Config::set('connection.syslog.zan_framework', $logConf);
    }
} 