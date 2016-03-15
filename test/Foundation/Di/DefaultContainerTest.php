<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 3/11/16
 * Time: 11:07
 */

namespace Foundation\Di;


use Foundation\Di\TestClass\DITestClassA;
use Foundation\Di\TestClass\DITestInterface;
use UnitTest;
use Zan\Framework\Foundation\Di\DefaultContainerBuilder;

class DefaultContainerTest extends UnitTest
{

    public function testBuilder()
    {
        $builder = new DefaultContainerBuilder();
        $container = $builder->getDefaultContainer();

        $container->define(DITestInterface::class, function () {
            return new DITestClassA();
        });

        $obj = $container->make(DITestInterface::class);
        var_dump($obj);
    }

}