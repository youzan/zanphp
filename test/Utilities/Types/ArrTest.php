<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/2
 * Time: 16:55
 */

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\Arr;

class ArrTest extends \TestCase {
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

    public function testMerge()
    {
        $arr1 = [
            'c1'	=> [
                'd1'	=> '1',
                'd2'	=> '2',
            ],
            'c2'	=> [
                'f1'	=> '3',
                'f2'	=> '4',
                'f3'	=> [1,2,3],
            ],
        ];

        $arr2 = [
            'c2'	=> [
                'f2'	=> '100',
                'f3'	=> [100, 200, 300],
                'f4'	=> '200',
            ],
            'c3'	=> [
                'h1'	=> 500,
                'h2'	=> 600,
            ],
        ];

        $arr = Arr::merge($arr1, $arr2);

        $this->assertArrayHasKey('c1', $arr, 'Arr::merge fail');
        $this->assertArrayHasKey('c2', $arr, 'Arr::merge fail');
        $this->assertArrayHasKey('c3', $arr, 'Arr::merge fail');

        $this->assertArrayHasKey('f1', $arr['c2'], 'Arr::merge fail');
        $this->assertArrayHasKey('f2', $arr['c2'], 'Arr::merge fail');
        $this->assertArrayHasKey('f3', $arr['c2'], 'Arr::merge fail');
        $this->assertArrayHasKey('f4', $arr['c2'], 'Arr::merge fail');

        $this->assertContains(100, $arr['c2']['f3'], 'Arr::merge fail');
        $this->assertNotContains(1, $arr['c2']['f3'], 'Arr::merge fail');
    }

    public function testCreateTreeByList(){
        $list = ['a',0,'c'];
        $arr = Arr::createTreeByList($list,'abc');
        $this->assertArrayHasKey('a', $arr, 'Arr::createTreeByList fail');
        $this->assertArrayHasKey(0, $arr['a'], 'Arr::createTreeByList fail');
        $this->assertArrayHasKey('c', $arr['a']['0'], 'Arr::createTreeByList fail');

        $this->assertEquals('abc', $arr['a'][0]['c'], 'Arr::sortByArray failed');

    }

}