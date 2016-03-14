<?php

namespace Zan\Framework\Foundation\Container;

use ReflectionClass;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class Di
{
    use Singleton;

    protected $instances = [];

    private function __construct()
    {

    }

    public function get($abstract)
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        return null;
    }

    public function set($alias, $instance)
    {
        if (!isset($this->instances[$alias])) {
            $this->instances[$alias] = $instance;
        }
    }

    public function make($abstract, array $parameters = [], $shared = false)
    {
        $abstract = $this->normalize($abstract);

        if ($shared && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $class = new ReflectionClass($abstract);
        $object = $class->newInstanceArgs($parameters);

        if ($shared && $object !== null) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Normalize the given class name by removing leading slashes.
     *
     * @param  string $className
     * @return string
     */
    protected function normalize($className)
    {
        return is_string($className) ? ltrim($className, '\\') : $className;
    }
}