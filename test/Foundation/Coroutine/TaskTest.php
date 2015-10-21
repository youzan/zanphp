<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/10/20
 * Time: 21:35
 */
namespace Zan\Framework\Test\Foundation\Coroutine;

require __DIR__ . '/../../../' . 'src/Zan.php';

class TaskTest extends \UnitTest {
    public function testOk()
    {
        $this->assertEquals(1,'1','ok...');

    }
}