<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 20:10
 */

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\Number;

class NumberTest extends \TestCase {
    public function testFloatToStingWork()
    {
        $float  = 0.03;
        $expect = '3';
        $result = Number::floatToString($float);

        $this->assertEquals($expect, $result, 'Number::floatToString fail');

        $float  = 1e7;
        $expect = '10000000';
        $result = Number::floatToString($float);

        $this->assertEquals($expect, $result, 'Number::floatToString fail');

        $float  = 3.1233;
        $expect = '31233';
        $result = Number::floatToString($float);

        $this->assertEquals($expect, $result, 'Number::floatToString fail');
    }
}