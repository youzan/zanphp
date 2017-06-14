<?php
namespace Zan\Framework\Utilities\Types;

use DateTime;

class Time
{
    private $timeStamp = null;

    public function __construct($timeStamp=null)
    {
        if(null !== $timeStamp && is_int($timeStamp)) {
            $this->timeStamp = $timeStamp;
            return true;
        }

        $this->timeStamp = time();
    }

    public static function current($format=false)
    {
        $timeStamp = time();

        if(true === $format){
            return $timeStamp;
        }

        if(false === $format){
            return date('Y-m-d H:i:s',$timeStamp);
        }

        return date($format,$timeStamp);
    }

    public static function stamp()
    {
        return self::current(true);
    }

    public static function future($seconds,$format=false)
    {
        $now    = self::current(true);
        $future = $now + $seconds;

        if(false === $format){
            return date('Y-m-d H:i:s', $future);
        }else if($format){
            return date($format,$future);
        }

        return $future;
    }

    public static function past($seconds,$format=false)
    {
        $seconds = -1 * $seconds;
        return self::future($seconds,$format);
    }

    public static function phpDiff($t1, $t2 = false, $format = '%y-%m-%d %h:%i:%s', $sign=false)
    {
        if (false === $t2) {
            $t2 = self::current();
        }

        $t1 = new DateTime($t1);
        $t2 = new DateTime($t2);
        $interval = $t2->diff($t1);

        if (true === $sign) {
            $format = '%r' . $format;
        }
        return $interval->format($format);
    }

    /**
     *  计算两个时间差值
     */
    public static function diff($t1, $t2 = false, $format = 'a', $sign = false)
    {
        if (strlen($format) !== 1) {
            return self::phpDiff($t1,$t2,$format,$sign);
        }

        $t1 = strtotime($t1);
        if (false === $t2) {
            $t2 = self::current(true);
        } else {
            $t2 = strtotime($t2);
        }

        $diff = $t1 - $t2;
        if (true === $sign) {
            $sign = ($diff > 0) ? 1 : -1;
            $diff = $sign * $diff;
        }

        switch (strtolower($format)) {
            case 'y' :
                $ret = floor($diff/365/12/24/60/60);
                break;
            case 'm' :
                $ret = floor($diff/12/24/60/60);
                break;
            case 'd' :
                $ret = floor($diff/24/60/60);
                break;
            case 'h' :
                $ret = floor($diff/60/60);
                break;
            case 'i' :
                $ret = floor($diff/60);
                break;
            case 's' :
                $ret = $diff;
                break;
            default :
                $ret = array(
                    'sign'      => ($diff > 0) ? 1 : -1,
                    'seconds'   => $diff,
                    'minutes'   => floor($diff/60),
                    'hours'     => floor($diff/60/60),
                    'days'      => floor($diff/24/60/60),
                    'monthes'   => floor($diff/30/24/60/60),
                    'years'     => floor($diff/365/30/24/60/60),
                );
        }

        return $ret;
    }
}