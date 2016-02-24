<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/23
 * Time: 12:05
 */

namespace Zan\Framework\Foundation\Core;


class Log
{
    /**
     * @param mixed $log [string,array,object]
     * @param string $logName
     * @return bool
     */
    public static function info($log, $logName = '')
    {
        return true;
    }

    public static function warning($log, $logName = '')
    {

    }

    public static function notice($log, $logName = '')
    {

    }

    public static function error($log, $logName = '')
    {
    }
}