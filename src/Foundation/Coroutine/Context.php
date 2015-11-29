<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/26
 * Time: 19:51
 */

namespace Zan\Framework\Foundation\Coroutine;


class Context {
    private $map = [];
    private $event = null;

    public function __construct() {
        $this->map = [];
        $this->event = new Event();
    }

    public function get($key, $default=null) {
        if( isset($this->map[$key]) ) {
           return $this->map[$key];
        }

        return $default;
    }

    public function set($key, $value) {
        $this->map[$key] = $value;
    }

    public function getEvent() {
        return $this->event;
    }

    public function getEventChain() {
        return $this->event->getEventChain();
    }
}