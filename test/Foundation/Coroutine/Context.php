<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/28
 * Time: 23:14
 */

namespace Zan\Framework\Test\Foundation\Coroutine;


class Context {
    private $map = [];

    public function __construct() {
        $this->map = [];
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

    public function show() {
        return $this->map;
    }
}