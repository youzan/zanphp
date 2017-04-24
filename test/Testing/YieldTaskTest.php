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
    private function simpleFunction()
    {
        return;
    }

    private function generator()
    {
        yield 3;
    }

    public function taskYield()
    {
        $a = (yield $this->simpleFunction());
        $this->assertEquals(NULL, $a, 'Yield simpleFunction return value test failed');

        $a = (yield $this->generator());
        $this->assertEquals(3, $a, 'Yield Generator test failed');
    }

}