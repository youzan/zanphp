<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/20
 * Time: 01:18
 */

namespace Zan\Framework\Test\Testing;


use Zan\Framework\Testing\TaskTest;

class YieldTaskTest extends TaskTest {
    public function taskYield()
    {
        $a = (yield 1);

        $this->assertEquals(1, $a, 'Yield Task test failed');
    }

}