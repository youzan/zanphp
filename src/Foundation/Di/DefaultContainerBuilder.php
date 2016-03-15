<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/14/16
 * Time: 11:09
 */

namespace Zan\Framework\Foundation\Di;


use Zan\Framework\Foundation\Di\ElementBuilder\ElementBuilder;
use Zan\Framework\Foundation\Di\ElementBuilder\DefaultElementBuilder;
use Zan\Framework\Foundation\Di\ElementPool\ElementPool;
use Zan\Framework\Foundation\Di\ElementPool\InMemoryElementPool;
use Zan\Framework\Foundation\Di\Exception\InvalidClassException;

class DefaultContainerBuilder implements ContainerBuilder
{

    /**
     * @var string
     */
    private $containerClass;

    /**
     * @var string
     */
    private $elementPoolClass;

    /**
     * @var string
     */
    private $elementFactoryClass;

    /**
     * @var Container
     */
    private $defaultContainer;

    /**
     * @var bool
     */
    private $autoWiring;

    /**
     * ContainerBuilder constructor.
     *
     * @param string $containerClass
     * @param string $elementPoolClass
     * @param string $elementFactoryClass
     * @param bool $autoWiring
     * @throws InvalidClassException
     */
    public function __construct($containerClass = null, $elementPoolClass = null, $elementFactoryClass = null, $autoWiring = false)
    {
        if (!($containerClass)) {
            $containerClass = DefaultContainer::class;
        } elseif (!is_a($containerClass, Container::class, true)) {
            throw new InvalidClassException();
        }
        if (!($elementPoolClass)) {
            $elementPoolClass = InMemoryElementPool::class;
        } elseif (!is_a($elementPoolClass, ElementPool::class, true)) {
            throw new InvalidClassException();
        }
        if (!($elementFactoryClass)) {
            $elementFactoryClass = DefaultElementBuilder::class;
        } elseif (!is_a($elementFactoryClass, ElementBuilder::class, true)) {
            throw new InvalidClassException();
        }

        $this->containerClass = $containerClass;
        $this->elementPoolClass = $elementPoolClass;
        $this->elementFactoryClass = $elementFactoryClass;

        $this->autoWiring = $autoWiring;

        $this->defaultContainer = $this->buildContainer();
    }

    /**
     * @return Container
     */
    private function buildContainer()
    {
        $containerClass = $this->containerClass;

        $container = $containerClass($this);

        return $container;
    }

    /**
     * @return Container
     */
    public function newContainerInstance()
    {
        return $this->buildContainer();
    }

    /**
     * @return Container
     */
    public function getDefaultContainer()
    {
        return $this->defaultContainer;
    }

    /**
     * @return string
     */
    public function getContainerClass()
    {
        return $this->containerClass;
    }


    /**
     * @return string
     */
    public function getElementPoolClass()
    {
        return $this->elementPoolClass;
    }


    /**
     * @return string
     */
    public function getElementFactoryClass()
    {
        return $this->elementFactoryClass;
    }

    /**
     * @return boolean
     */
    public function isAutoWiring()
    {
        return $this->autoWiring;
    }


}