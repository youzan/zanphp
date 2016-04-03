<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/3
 * Time: 12:53
 */

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\Func;

class FuncTest extends \TestCase
{
    public function testCallWork()
    {
        $func = function($arg){
            return $arg;
        };

        $expect = 'demo func';
        $output = Func::call($func, $expect);

        $this->assertEquals($expect, $output, 'Func::call fail');
    }

    public function testToClosureWork()
    {
        $func = function($arg){
            return $arg;
        };

        $validator = function(){
            if(func_num_args() === 2) {
                return true;
            }

            return false;
        };

        $arg = 'func closure';
        $closure = Func::toClosure($func, $arg, $validator);

        $output = $closure();
        $this->assertNull($output, 'Func::toClosure work fail');

        $output = $closure(1,2);
        $this->assertNotNull($output, 'Func::toClosure work fail');
        $this->assertEquals($arg, $output, 'Func::toClosure work fail');
    }
}