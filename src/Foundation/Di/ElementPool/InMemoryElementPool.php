<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/11/16
 * Time: 10:52
 */

namespace Zan\Framework\Foundation\Di\ElementPool;


use Zan\Framework\Foundation\Di\Container;
use Zan\Framework\Foundation\Di\ElementDefinition;
use Zan\Framework\Foundation\Di\Exception\InvalidDefinitionException;

class InMemoryElementPool implements ElementPool
{

    /**
     * @var ElementDefinition[]
     */
    private $pool = [];

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @return ElementDefinition
     * @throws InvalidDefinitionException
     */
    public function get($name)
    {
        if (isset($this->pool[$name])) {
            return $this->pool[$name];
        } else {
            throw new InvalidDefinitionException('Element definition not exists');
        }
    }

    /**
     * @param $name
     * @param $instance
     * @param $constructor
     * @param $deferred
     * @param $scope
     * @throws InvalidDefinitionException
     */
    public function set($name, $instance, $constructor, $deferred, $scope)
    {
        $definition = new ElementDefinition();

        if (!$name) {
            throw new InvalidDefinitionException('Element definition must have name');
        }

        if ($name) {
            $definition->setName($name);
            $this->pool[$name] = $definition;
        }

        if ($instance) {
            if ($deferred) {
                throw new InvalidDefinitionException('Cannot define a deferred element with an instance exists');
            }
            $definition->setInstance($instance);
        }

        if ($constructor) {
            $definition->setConstructor($constructor);
        } else {
            $definition->setConstructor([$name, '__construct']);
        }

        $definition->setLazyInitialize($deferred)->setScope($scope);

        if ($deferred and $scope == Container::ELEMENT_SCOPE_SINGLETON) {
            $this->container->make($name);
        }
    }
}