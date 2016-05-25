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

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\Func;

class FuncTest extends \TestCase
{
    public function testCallWork()
    {
        $func = function($arg){
            return $arg;
        };

        $expect = 'demo func';
        $output = Func::call($func, $expect);

        $this->assertEquals($expect, $output, 'Func::call fail');
    }

    public function testToClosureWork()
    {
        $func = function($arg){
            return $arg;
        };

        $validator = function(){
            if(func_num_args() === 2) {
                return true;
            }

            return false;
        };

        $arg = 'func closure';
        $closure = Func::toClosure($func, $arg, $validator);

        $output = $closure();
        $this->assertNull($output, 'Func::toClosure work fail');

        $output = $closure(1,2);
        $this->assertNotNull($output, 'Func::toClosure work fail');
        $this->assertEquals($arg, $output, 'Func::toClosure work fail');
    }
}