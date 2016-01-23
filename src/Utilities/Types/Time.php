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

    public static function current()
    {

    }

}