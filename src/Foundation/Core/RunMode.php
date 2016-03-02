<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/2
 * Time: 17:44
 */

namespace Zan\Framework\Foundation\Core;


use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class RunMode {
    private static $modeMap  = [
        'dev'       => 1,
        'test'      => 2,
        'pre'       => 3,
        'readonly'  => 4,
        'online'    => 5,
        'unittest'  => 6,
    ];
    private static $runMode = null;
    private static $cliInput = null;

    public static function get()
    {
        return self::$runMode;
    }

    public static function setCliInput($mode)
    {
        if(!$mode){
            return false;
        }

        if(!isset(self::$modeMap[$mode])){
            throw new InvalidArgument('invlid runMode from cli');
        }
        self::$cliInput = $mode;
    }

    public static function detect()
    {
        if(null !== self::$cliInput){
            self::$runMode = self::$cliInput;
            return true;
        }

        $iniInput = get_cfg_var('kdt.RUN_MODE');
        if(isset(self::$modeMap[$iniInput])){
            self::$runMode = $iniInput;
            return true;
        }

        self::$runMode = 'online';
    }
}