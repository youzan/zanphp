<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/2/25
 * Time: 15:45
 */

namespace Zan\Framework\Foundation\Core;

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