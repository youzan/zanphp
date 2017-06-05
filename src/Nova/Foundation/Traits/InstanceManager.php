<?php

namespace Kdt\Iron\Nova\Foundation\Traits;

trait InstanceManager
{
    /**
     * @var static
     */
    private static $objectInstance = null;

    /**
     * @return static
     */
    final public static function instance()
    {
        if (is_null(self::$objectInstance))
        {
            self::$objectInstance = self::newInstance();
        }
        return self::$objectInstance;
    }

    /**
     * @return InstanceManager
     */
    final public static function getInstance()
    {
        return self::instance();
    }

    /**
     * @return static
     */
    final public static function newInstance()
    {
        return new static();
    }
}