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
}