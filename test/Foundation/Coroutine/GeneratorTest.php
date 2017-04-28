<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/21
 * Time: 21:13
 */

namespace Zan\Framework\Test\Foundation\Coroutine;

class GeneratorTest extends Base {
    protected function step()
    {
        $a = (yield 1);

        $this->assertEquals(1, $a, 'fail');
    }
}
