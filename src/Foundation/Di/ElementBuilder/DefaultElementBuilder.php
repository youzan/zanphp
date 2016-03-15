<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/13/16
 * Time: 20:54
 */

namespace Zan\Framework\Foundation\Di\ElementBuilder;


use ReflectionFunction;
use ReflectionMethod;
use Zan\Framework\Foundation\Di\ElementDefinition;
use Zan\Framework\Foundation\Di\Container;
use Zan\Framework\Foundation\Di\Exception\DependencyException;
use Zan\Framework\Foundation\Di\Exception\InvalidDefinitionException;

class DefaultElementBuilder implements ElementBuilder
{

    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param ElementDefinition $definition
     * @return mixed|object
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function makeElement(ElementDefinition $definition)
    {
        $targetInstance = $definition->getInstance();

        if ($targetInstance) {
            switch ($definition->getScope()) {
                case Container::ELEMENT_SCOPE_SINGLETON:
                    return $targetInstance;
                    break;
                case Container::ELEMENT_SCOPE_PROTOTYPE:
                    return clone $targetInstance;
                    break;
                default:
                    throw new InvalidDefinitionException('Unknown scope type');
            }
        }

        $constructor = $definition->getConstructor();
        if (!($constructor)) {
            $constructor = [$definition->getName(), '__construct'];
        }

        $targetInstance = $this->invokeConstructor($constructor);

        if ($definition->getScope() == Container::ELEMENT_SCOPE_SINGLETON) {
            $definition->setInstance($targetInstance);
        }

        return $targetInstance;
    }

    /**
     * @param callable $constructor
     * @return mixed
     * @throws DependencyException
     */
    private function invokeConstructor(Callable $constructor)
    {
        $reflection = $this->parseCallable($constructor);
        $parameters = $reflection->getParameters();
        $realParams = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getDeclaringClass();
            if (class_exists($name, true)) {
                $realParam = $this->container->make($name);
                $realParams[$parameter->getPosition()] = $realParam;
            } else {
                throw new DependencyException('Class not exists');
            }
        }
        $targetInstance = $reflection->invokeArgs($realParams);
        return $targetInstance;
    }

    /**
     * @param callable $func
     * @return ReflectionMethod|ReflectionFunction
     */
    private function parseCallable(Callable $func)
    {
        if (is_array($func)) {
            return new ReflectionMethod($func[0], $func[1]);
        } else {
            return new ReflectionFunction($func);
        }
    }


}