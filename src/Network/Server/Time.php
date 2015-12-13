<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:20
 */

namespace Zan\Framework\Network\Server;


class Time {
    private $ts = 0;
    private $ms = 0.00;

    public function __construct()
    {
        $this->getCurrentTimeStamp();
    }

    public function current($format=null, $useCache=true)
    {
        if (false === $useCache) {
            $this->getCurrentTimeStamp();
        }
    }


    private function getCurrentTimeStamp()
    {
        $timeString = microtime();
        list($this->ms, $this->ts) = explode(" ", $timeString);
    }

}