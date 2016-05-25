<?php
namespace Zan\Framework\Utilities\Types;

class Time
{
    private $timeStamp = null;
    public function __construct($timeStamp=null)
    {
        if(null !== $timeStamp && is_int($timeStamp)) {
            $this->timeStamp = $timeStamp;
            return true;
        }

        $this->timeStamp = \nova_get_time();;
    }

    public static function current($format=false)
    {
        $timeStamp = \nova_get_time();

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

}