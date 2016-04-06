<?php
namespace Zan\Framework\Utilities\DesignPattern;

trait Singleton {

    /**
     * @var static
     */
    protected static $_instances = [];

    /**
     * @return static
     */
    final public static function instance()
    {
        $class = get_called_class();
        if (!isset(static::$_instances[$class]) or null === static::$_instances[$class]) {
            static::$_instances[$class] = new $class();
        }
        return static::$_instances[$class];
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
        static::$_instances[get_class($instance)] = $instance;
    }

    final private function __construct()
    {
    }
}