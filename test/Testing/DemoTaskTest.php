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

namespace Zan\Framework\Test\Testing;


use Zan\Framework\Testing\TaskTest;

class DemoTaskTest extends TaskTest {
    protected $counter = 0;
    public function taskTest1()
    {
        $this->counter++;
        $this->assertEquals(1, $this->counter, 'Task test fail: taskTest1');
    }

    protected function taskTest2()
    {
        $this->counter++;
        $this->assertEquals(2, $this->counter, 'Task test fail: taskTest2');
    }

    protected function taskTest3()
    {
        $this->counter++;
        $this->assertEquals(3, $this->counter, 'Task test fail: taskTest3');
    }
}