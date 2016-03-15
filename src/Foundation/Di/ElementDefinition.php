<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/14/16
 * Time: 15:21
 */

namespace Zan\Framework\Foundation\Di;


class ElementDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $scope;

    /**
     * @var boolean
     */
    private $lazyInitialize;

    /**
     * @var callable
     */
    private $constructor;

    /**
     * @var object
     */
    private $instance;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ElementDefinition
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $alias
     * @return ElementDefinition
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param integer $scope
     * @return ElementDefinition
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLazyInitialize()
    {
        return $this->lazyInitialize;
    }

    /**
     * @param boolean $lazyInitialize
     * @return ElementDefinition
     */
    public function setLazyInitialize($lazyInitialize)
    {
        $this->lazyInitialize = $lazyInitialize;
        return $this;
    }

    /**
     * @return callable
     */
    public function getConstructor()
    {
        return $this->constructor;
    }

    /**
     * @param callable $constructor
     * @return ElementDefinition
     */
    public function setConstructor($constructor)
    {
        $this->constructor = $constructor;
        return $this;
    }

    /**
     * @return object
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param object $instance
     * @return ElementDefinition
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }
}