<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Singleton {
    private static $_instance = null;

    public static function instance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function getInstance() {
        return self::getInstance();
    }
}