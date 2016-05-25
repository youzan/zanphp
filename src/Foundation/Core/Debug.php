<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/2
 * Time: 17:44
 */

namespace Zan\Framework\Foundation\Core;

class Debug {

    private static $debug = null;
    private static $cliInput = null;

    public static function get()
    {
        return self::$debug;
    }

    public static function setCliInput($mode)
    {
        self::$cliInput == $mode ? true : false;
    }

    public static function detect()
    {
        if(null !== self::$cliInput){
            self::$debug = self::$cliInput;
            return true;
        }

        $iniInput = get_cfg_var('zan.DEBUG');
        if($iniInput){
            self::$debug = true;
            return true;
        }

        self::$debug = false;
    }
}