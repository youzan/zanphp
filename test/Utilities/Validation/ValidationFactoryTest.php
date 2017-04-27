<?php

namespace Zan\Framework\Test\Utilities\Validation;

use Zan\Framework\Utilities\Validation\Factory;
use Zan\Framework\Utilities\Validation\Validator;

class ValidationFactoryTest extends \TestCase
{
    public function testMakeMethodCreatesValidValidator()
    {
        $validator = Factory::make(['foo' => 'bar'], ['baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar'], $validator->getData());
        $this->assertEquals(['baz' => ['boom']], $validator->getRules());
    }

    public function testCustomResolverIsCalled()
    {
        unset($_SERVER['__validator.factory']);
        $factory = Factory::getInstance();
        $factory->resolver(function ($data, $rules) {
            $_SERVER['__validator.factory'] = true;

            return new Validator($data, $rules);
        });
        $validator = $factory->make(['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertTrue($_SERVER['__validator.factory']);
        $this->assertEquals(['foo' => 'bar'], $validator->getData());
        $this->assertEquals(['baz' => ['boom']], $validator->getRules());
        unset($_SERVER['__validator.factory']);
    }
}
