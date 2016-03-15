<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/13/16
 * Time: 20:46
 */

namespace Zan\Framework\Foundation\Di\ElementBuilder;


use Zan\Framework\Foundation\Di\Container;
use Zan\Framework\Foundation\Di\ElementDefinition;
use Zan\Framework\Foundation\Di\Exception\DependencyException;
use Zan\Framework\Foundation\Di\Exception\InvalidDefinitionException;

interface ElementBuilder
{

    /**
     * ElementBuilder constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container);

    /**
     * Assemble an element
     *
     * @param ElementDefinition $definition
     * @return object
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function makeElement(ElementDefinition $definition);

}