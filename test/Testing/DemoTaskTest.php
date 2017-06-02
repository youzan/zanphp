<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/20
 * Time: 01:18
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