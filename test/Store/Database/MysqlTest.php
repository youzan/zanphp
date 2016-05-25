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


namespace Zan\Framework\Test\Store\Database;
use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Store\Facade\Db;
class MysqlTest extends TaskTest
{
    public function testInit()
    {
        //sql map
        //table
        //connection
    }

    public function testInsert()
    {
        $sid = '';
        $data = [];
        $data = (yield Db::execute());
        $this->assertGreaterThan(0, $data);
    }

    public function testSelectOne()
    {
        $context = new Context();
        $job = new SelectJob($context);
        $sid = 'demo.demo_sql_id1_1';
        $data = [
            'var' => ['name' => 'a', 'nick_name' => 'b'],
            'and' => [
                ['gender', '=', 1],
            ],
            'and1' => [
                ['id_number', '=', 2147483647],
            ],
            'limit' => '1'

        ];
        $options = [];
        $job->setSid($sid)->setData($data)->setOptions($options);
        $coroutine = $job->run();
        $task = new Task($coroutine);
        $task->run();
        $result = $context->show();
    }
}