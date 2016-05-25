<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Test\Utilities\Validation;

use Mockery as m;
use Zan\Framework\Utilities\Validation\Factory;
use Zan\Framework\Utilities\Validation\Validator;

class ValidationFactoryTest extends \TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testMakeMethodCreatesValidValidator()
    {
        //$factory = new Factory();
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
