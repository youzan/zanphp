<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Singleton
{

    /**
     * @var static
     */
    private static $_instance = null;

    /**
     * @return static
     */
    final public static function instance()
    {
        return static::singleton();
    }
    
    final public static function singleton()
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
        return static::singleton();
    }

    final public static function swap($instance)
    {
        static::$_instance = $instance;
    }
}