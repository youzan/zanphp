<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Singleton {

    /**
     * @var static
     */
    private static $_instance = null;

    /**
     * @return static
     */
    final public static function instance()
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @return static
     */
    final public static function getInstance()
    {
        return static::instance();
    }

    final public static function swap($instance)
    {
        static::$_instance = $instance;
    }

    final private function __construct()
    {
    }
}