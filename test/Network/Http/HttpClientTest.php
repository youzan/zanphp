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

namespace Zan\Framework\Test\Network\Http;

use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Testing\TaskTest;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Test\Foundation\Coroutine\Context;

class HttpClientTest extends TaskTest
{



    public function testTaskCall()
    {
        $context = new Context();
        $task = new Task($this->makeCoroutine($context), null, 8);
        $task->run();

    }

    private function makeCoroutine($context)
    {
        $params = [
            'txt' => 'aaa',
            'size' => 200,
            'margin' => 20,
            'level' => 0,
            'hint' => 2,
            'case' => 1,
            'ver' => 1,
            'fg_color' => '000000',
            'bg_color' => 'ffffff',
        ];
        $result = (yield HttpClient::newInstance('192.168.66.202', 8888)->get('', $params));

        var_dump($result);
        exit;

        yield 'success';
    }
}