<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Singleton {
    private static $_instance = null;

    public static function instance() {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public static function getInstance() {
        return static::instance();
    }
}