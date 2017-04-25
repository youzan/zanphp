<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/2
 * Time: 17:44
 */

namespace Zan\Framework\Foundation\Core;

class Debug
{

    private static $debug = null;

    public static function get()
    {
        return self::$debug;
    }

    public static function enableDebug()
    {
        self::$debug = true;
    }

    public static function disableDebug()
    {
        self::$debug = false;
    }

    public static function detect()
    {
        if(null !== self::$debug){
            return;
        }

        $iniInput = get_cfg_var('kdt.DEBUG');
        if($iniInput){
            self::$debug = true;
            return;
        }

        self::$debug = false;
    }
}