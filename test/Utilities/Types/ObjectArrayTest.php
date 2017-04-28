<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 20:10
 */

namespace Zan\Framework\Test\Utilities\Types;
use Zan\Framework\Utilities\Types\ObjectArray;

class UserTest {
    public $a = 1;
    public $b = 2;
}

class ObjectArrayTest extends \TestCase {

    private $arrayObject = null;

    public function testArrayObjectWork()
    {
        $this->arrayObject = new ObjectArray();
        $o1 = new UserTest();
        $o2 = new UserTest();
        $o3 = new UserTest();

        $this->arrayObject->push($o1);
        $this->arrayObject->push($o2);
        $this->arrayObject->push($o3);

        $obj = $this->arrayObject->pop();

        $this->assertEquals($o3, $obj);
        $obj = $this->arrayObject->pop();
        $this->assertEquals($o2, $obj);
        $obj = $this->arrayObject->pop();
        $this->assertEquals($o1, $obj);
    }
}