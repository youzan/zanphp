<?php

namespace Zan\Framework\Foundation\Core;

class RunMode
{
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
        self::$runMode = $runMode;
        putenv("runMode=$runMode");
    }

    public static function detect()
    {
        if (null !== self::$runMode) {
            return;
        }

        $envInput = getenv('KDT_RUN_MODE');
        if ($envInput !== false) {
            self::set($envInput);
            return;
        }

        $iniInput = get_cfg_var('kdt.RUN_MODE');
        if ($iniInput !== false) {
            self::set($iniInput);
            return;
        }

        $envInput = getenv('ZANPHP_RUN_MODE');
        if ($envInput !== false) {
            self::set($envInput);
            return;
        }

        $iniInput = get_cfg_var('zanphp.RUN_MODE');
        if ($iniInput !== false) {
            self::set($iniInput);
            return;
        }

        self::set('online');
    }

    public static function isOnline()
    {
        return in_array(self::$runMode, ["pre", "online"], true);
    }
}
