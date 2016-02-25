<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/2
 * Time: 16:55
 */

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\Arr;

require __DIR__ . '/../../../' . 'src/Test.php';


class ArrTest extends \UnitTest {
    public function testArrJoin() {
        $before = [0, 1, 2];
        $after  = ['a', 'b', 'c'];

        $result = Arr::join($before,$after);

        $this->assertArrayHasKey(3, $result, 'Arr::join failed');
        $this->assertEquals('a', $result[3], 'Arr::join failed: no after keys');
        $this->assertEquals(6, count($result), 'Arr::join failed: after key losts');
    }
}