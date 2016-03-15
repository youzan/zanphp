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
    public function testArrJoin()
    {
        $before = [0, 1, 2];
        $after  = ['a', 'b', 'c'];

        $result = Arr::join($before,$after);

        $this->assertArrayHasKey(3, $result, 'Arr::join failed');
        $this->assertEquals('a', $result[3], 'Arr::join failed: no after keys');
        $this->assertEquals(6, count($result), 'Arr::join failed: after key losts');
    }

    public function testSortByArray()
    {
        $arr = ['a', 'd', 'e', 'h'];
        $sort = ['h', 'a'];

        $ret = Arr::sortByArray($arr, $sort);
        $this->assertArrayHasKey(0, $ret, 'Arr::sortByArray failed');
        $this->assertEquals('h', $ret[0], 'Arr::sortByArray failed');

        $this->assertArrayHasKey(2, $ret, 'Arr::sortByArray failed');
        $this->assertEquals('d', $ret[2], 'Arr::sortByArray failed');


        $arr = [5, 7, 13, 39];
        $sort = [13, 12, 7];

        $ret = Arr::sortByArray($arr, $sort, true);

        $this->assertArrayHasKey('result', $ret, 'Arr::sortByArray failed');
        $this->assertArrayHasKey('notExist', $ret, 'Arr::sortByArray failed');

        $this->assertArrayHasKey(0, $ret['result'], 'Arr::sortByArray failed');
        $this->assertEquals(13, $ret['result'][0], 'Arr::sortByArray failed');

        $this->assertArrayHasKey(1, $ret['result'], 'Arr::sortByArray failed');
        $this->assertEquals(7, $ret['result'][1], 'Arr::sortByArray failed');

        $this->assertArrayHasKey(0, $ret['notExist'], 'Arr::sortByArray failed');
        $this->assertEquals(12, $ret['notExist'][0], 'Arr::sortByArray failed');
    }
}