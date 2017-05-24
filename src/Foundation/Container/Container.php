<?php

namespace Zan\Framework\Foundation\Container;

use ReflectionClass;
use Zan\Framework\Testing\Stub;

class Container
{
    protected $mockInstances = [];

    protected $instances = [];

    public function get($abstract)
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->mockInstances[$abstract])) {
            return $this->mockInstances[$abstract];
        }

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

    public function setMockInstance($abstract, $instance)
    {
        $abstract = $this->normalize($abstract);
        $this->mockInstances[$abstract] = $instance;
    }

    public function addStub(Stub $stub)
    {
        $className = $stub->getRealClassName();

        $this->setMockInstance($className, $stub);
    }

    public function cleanStub()
    {
        $this->mockInstances = [];
    }

    public function singleton($abstract, array $parameters = [])
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->mockInstances[$abstract])) {
            return $this->mockInstances[$abstract];
        }

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $class = new ReflectionClass($abstract);
        $object = $class->newInstanceArgs($parameters);

        if ($object !== null) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function make($abstract, array $parameters = [], $shared = false)
    {
        $abstract = $this->normalize($abstract);

        if (isset($this->mockInstances[$abstract])) {
            return $this->mockInstances[$abstract];
        }

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
