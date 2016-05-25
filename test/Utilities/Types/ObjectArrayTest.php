<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

namespace Zan\Framework\Test\Utilities\Types;

use Zan\Framework\Utilities\Types\ObjectArray;


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

        //$ret = $this->arrayObject->pop();
        //var_dump($ret);

        //$this->arrayObject->remove($ret);
        $length = $this->arrayObject->length();
        for ($i =0; $i<$length; $i++) {
            $ret = $this->arrayObject->pop();
            var_dump($ret);

        }

    }

}