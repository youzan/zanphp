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

namespace Zan\Framework\Test\Foundation\Coroutine;

use Zan\Framework\Testing\TaskTestBase;

class TaskDemoTest extends TaskTestBase {

    /**
     * 如果只有一个测试任务，就直接写在taskStep里面，
     * 如果有多个，那么就可以在taskStep里面添加需要
     * 执行的测试方法；
     */
    public function taskStep()
    {
        $this->addTestAction([
            'step1',
            'step2'
        ]);
    }

    public function step1()
    {
        $a = (yield 2);

        $this->assertEquals(2, $a, 'fail');
    }

    public function step2()
    {
        $a = (yield 3);

        $this->assertEquals(3, $a, 'fail');
    }
}

