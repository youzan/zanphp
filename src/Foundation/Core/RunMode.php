<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/2
 * Time: 17:44
 */

namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class RunMode {
    private static $modeMap  = [
        'dev'       => 1,
        'test'      => 2,
        'pre'       => 3,
        'readonly'  => 4,
        'online'    => 5,
        'unittest'  => 6,
        'qatest'    => 7,
        'pubtest'   => 8,
        'ci'        => 9,
        'perf'      => 10,
        'daily'     => 11,
    ];
    private static $runMode = null;

    public static function get()
    {
        return self::$runMode;
    }

    public static function set($runMode)
    {
        if (!isset(self::$modeMap[$runMode])) {
            throw new InvalidArgumentException('invalid runMode in RunMode::set');
        }
        self::$runMode = $runMode;
    }

    public static function detect()
    {
        if (null !== self::$runMode) {
            return true;
        }

        $envInput = getenv('KDT_RUN_MODE');
        if (isset(self::$modeMap[$envInput])) {
            self::$runMode = $envInput;
            return true;
        }

        $iniInput = get_cfg_var('kdt.RUN_MODE');
        if (isset(self::$modeMap[$iniInput])) {
            self::$runMode = $iniInput;
            return true;
        }

        self::$runMode = 'online';
    }
}
