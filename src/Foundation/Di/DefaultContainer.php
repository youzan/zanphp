<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/11/16
 * Time: 10:50
 */

namespace Zan\Framework\Foundation\Di;


use Zan\Framework\Foundation\Di\ElementBuilder\ElementBuilder;
use Zan\Framework\Foundation\Di\ElementPool\ElementPool;

class DefaultContainer implements Container
{

    /**
     * @var ContainerBuilder
     */
    private $builder;

    /**
     * @var ElementPool
     */
    private $pool;

    /**
     * @var ElementBuilder
     */
    private $factory;

    public function __construct(ContainerBuilder $builder)
    {
        $this->builder = $builder;
        $pool = $builder->getElementPoolClass();
        $factory = $builder->getElementFactoryClass();

        $this->pool = new $pool($this);
        $this->factory = new $factory($this);
    }

    /**
     * Set an element by interface name or class name
     *
     * @param $name
     * @param object|callable $obj
     * @param bool $lazyInitialize
     * @param integer $scope
     * @return mixed
     */
    public function define($name, $obj = null, $lazyInitialize = false, $scope = self::ELEMENT_SCOPE_SINGLETON)
    {
        if (is_callable($obj)) {
            $this->pool->set($name, null, $obj, $lazyInitialize, $scope);
        } else {
            $this->pool->set($name, $obj, null, $lazyInitialize, $scope);
        }
    }

    public function make($name)
    {
        $definition = $this->pool->get($name);
        $obj = $this->factory->makeElement($definition);
        return $obj;
    }

    /**
     * Pre set an element by interface name or class name
     *
     * @param $name
     * @param callable $constructor
     * @param int $scope
     * @return mixed
     */
    public function preDefine($name, callable $constructor = null, $scope = self::ELEMENT_SCOPE_SINGLETON)
    {
        $this->pool->set($name, null, $constructor, true, $scope);
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder()
    {
        return $this->builder;
    }
}