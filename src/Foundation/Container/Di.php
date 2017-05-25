<?php

namespace Zan\Framework\Foundation\Container;

use RuntimeException;
use Zan\Framework\Testing\Stub;

class Di
{
    /**
     * The resolved object instances.
     *
     * @var \Zan\Framework\Foundation\Container\Container
     */
    protected static $instance;

    /**
     * set the underlying instance behind the facade.
     *
     * @param  \Zan\Framework\Foundation\Container\Container  $instance
     */
    public static function resolveFacadeInstance(Container $instance)
    {
        static::$instance = $instance;
    }

    /**
     * @param $abstract
     * @param array $parameters
     * @param bool $shared
     * @return mixed|object
     */
    public static function make($abstract, array $parameters = [], $shared = false) {
        return static::$instance->make($abstract, $parameters, $shared);
    }
    
    public static function addStub(Stub $stub)
    {
        return static::$instance->addStub($stub);
    }

    public static function cleanStub()
    {
        static::$instance->cleanStub();
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::$instance;

        if (! $instance) {
            throw new RuntimeException('A facade instance has not been set.');
        }

        return $instance->$method(...$args);
    }
}