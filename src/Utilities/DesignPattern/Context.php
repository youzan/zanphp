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

namespace Zan\Framework\Utilities\DesignPattern;

use Zan\Framework\Foundation\Coroutine\Event;

class Context
{
    private $map = [];
    private $event = null;

    public function __construct()
    {
        $this->map = [];
        $this->event = new Event();
    }

    public function get($key, $default = null, $class = null)
    {
        if (!isset($this->map[$key])) {
            return $default;
        }

        if (null === $class) {
            return $this->map[$key];
        }

        if ($this->map[$key] instanceof $class
            || is_subclass_of($this->map[$key], $class)
        ) {
            return $this->map[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->map[$key] = $value;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getEventChain()
    {
        return $this->event->getEventChain();
    }
}