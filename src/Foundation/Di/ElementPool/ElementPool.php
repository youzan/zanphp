<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/11/16
 * Time: 10:40
 */

namespace Zan\Framework\Foundation\Di\ElementPool;

use Zan\Framework\Foundation\Di\Container;
use Zan\Framework\Foundation\Di\ElementDefinition;
use Zan\Framework\Foundation\Di\Exception\InvalidDefinitionException;

interface ElementPool
{
    /**
     * ElementPool constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container);

    /**
     * Get an element definition from pool
     *
     * @param $name
     * @return ElementDefinition
     * @throws InvalidDefinitionException
     */
    public function get($name);

    /**
     * Set an element definition
     *
     * @param $name
     * @param $instance
     * @param $constructor
     * @param $deferred
     * @param $scope
     * @throws InvalidDefinitionException
     */
    public function set($name, $instance, $constructor, $deferred, $scope);

}