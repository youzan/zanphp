<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/11/16
 * Time: 10:32
 */

namespace Zan\Framework\Foundation\Di;

interface Container
{

    const ELEMENT_SCOPE_SINGLETON = 1;
    const ELEMENT_SCOPE_PROTOTYPE = 2;

    /**
     * Container constructor.
     * @param ContainerBuilder $builder
     */
    public function __construct(ContainerBuilder $builder);

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder();

    /**
     * Assemble an element by interface name or class name
     *
     * @param $name
     * @return mixed
     */
    public function make($name);

    /**
     * Define an element by interface name or class name
     *
     * @param $name
     * @param object|callable $obj
     * @param bool $lazyInitialize
     * @param integer $scope
     * @return mixed
     */
    public function define($name, $obj = null, $lazyInitialize = false, $scope = self::ELEMENT_SCOPE_SINGLETON);

    /**
     * Define a deferred element by interface name or class name
     *
     * @param $name
     * @param callable $constructor
     * @param int $scope
     * @return mixed
     */
    public function preDefine($name, callable $constructor = null, $scope = self::ELEMENT_SCOPE_SINGLETON);

}