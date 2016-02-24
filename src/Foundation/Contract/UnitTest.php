<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/22
 * Time: 15:26
 */

namespace Zan\Framework\Foundation\Contract;


class UnitTest extends \PHPUnit_Framework_TestCase
{

    protected function invoke(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

}