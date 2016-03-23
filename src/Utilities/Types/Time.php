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

        $this->timeStamp = time();
    }

    public static function current($format=false)
    {
        $timeStamp = time();
        if(false === $format){
            return date('Y-m-d H:i:s',$timeStamp);
        }

        return date($format,$timeStamp);
    }

}