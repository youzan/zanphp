<?php
/*
 * |---------------------|
 * |  task testing demo  |
 * |---------------------|
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

