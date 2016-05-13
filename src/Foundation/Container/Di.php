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

        switch (count($args)) {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}