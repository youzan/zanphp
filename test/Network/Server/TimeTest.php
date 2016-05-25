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

namespace Zan\Framework\Test\Network\Server;

require __DIR__ . '/../../../' . 'src/Test.php';

use Zan\Framework\Network\Server\Time;


class TimeTest extends \UnitTest {
    public function testTimeFormatWorkFine()
    {
        $time = new Time();
        $ts = time();

        $result = $time->format('U', $ts);
        $expect = date('U', $ts);

        $this->assertEquals($expect, $result, 'Time.format fail');
        $this->setExpectedException('Zan\\Framework\\Foundation\\Exception\\System\\InvalidArgument');
        $time->format('U');
    }
}