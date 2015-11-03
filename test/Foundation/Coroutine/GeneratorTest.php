<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/21
 * Time: 21:13
 */

namespace Zan\Framework\Test\Foundation\Coroutine;


class GeneratorTest extends \PHPUnit_Framework_TestCase {
    public function testAsynNumWork() {
        $gen = $this->asynNum();

        $value = $gen->current();
        $this->assertEquals(1, $value, ' yield num error happened!');

        $gen->send(2);
        $value = $gen->current();
        $this->assertNull($value, 'asynNum do not return null after send');
    }




    private function asynNum() {
        yield 1;
    }



}
