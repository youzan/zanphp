<?php

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

    public function clear()
    {
        foreach ($this->map as $value) {
            unset($value);
        }
        unset($this->map);
        $this->map = null;
        $this->event = null;
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