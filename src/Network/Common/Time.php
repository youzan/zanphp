<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Network\Server;


use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Time {
    private $ts = 0;
    private $ms = 0.00;
    private $timeUsed = false;

    public function __construct()
    {
        $this->getCurrentTimeStamp();
    }

    public function current($format='U', $useCache=true)
    {
        $this->timeUsed = true;
        if (false === $useCache) {
            $this->getCurrentTimeStamp();
        }

        return $this->format($format, $this->ts, $this->ms);
    }

    public function tick()
    {
        $this->getCurrentTimeStamp();
        Event::fire('clock_tick');
    }

    public function unique($errorRange=100, $withMs=true)
    {

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

        $this->timeUsed = false;
    }


}