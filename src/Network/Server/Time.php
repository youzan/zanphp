<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 01:20
 */

namespace Zan\Framework\Network\Server;


use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Time {
    private $ts = 0;
    private $ms = 0.00;

    public function __construct()
    {
        $this->getCurrentTimeStamp();
    }

    public function current($format='U', $useCache=true)
    {
        if (false === $useCache) {
            $this->getCurrentTimeStamp();
        }

        return $this->format($format, $this->ts, $this->ms);
    }

    public function format($format='U', $ts=null, $ms=null)
    {
        if(!$ts) {
            throw new InvalidArgument('Invalid ts for Time.format()');
        }
        return date($format, $ts);
    }

    private function getCurrentTimeStamp()
    {
        $timeString = microtime();
        list($this->ms, $this->ts) = explode(" ", $timeString);
    }


}