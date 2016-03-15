<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/14/16
 * Time: 20:32
 */

namespace Zan\Framework\Foundation\Di;


interface ContainerBuilder
{

    public function __construct($containerClass = null, $elementPoolClass = null, $elementFactoryClass = null);

    public function getContainerClass();

    public function getElementFactoryClass();

    public function getElementPoolClass();

    public function getDefaultContainer();

    public function isAutoWiring();

    public function newContainerInstance();
}